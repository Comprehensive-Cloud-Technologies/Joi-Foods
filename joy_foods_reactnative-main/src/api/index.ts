import { BASE_URL } from '@env';
import ReactNativeBlobUtil from 'react-native-blob-util';
import { storage } from 'store/mmkv';
import { DeviceEventEmitter } from 'react-native';

// Public API key for the `Auth` header — hardcoded (same as the store & guest
// apps) so a missing/empty .env value in a release build can never break auth.
const AUTH_TOKEN =
  'd14e5fe6050fda6693e8f658a80879f236e36da3173b4009d56b8100e3782646';

const headR = (token?: string, isFormData?: boolean, acceptJSON = true) => {
  // Use a plain object, not `new Headers()`: on React Native (esp. with a
  // FormData body) the Headers object can drop custom headers like `Auth`.
  const headers: Record<string, string> = {};
  if (acceptJSON) headers['Accept'] = 'application/json';
  if (!isFormData) {
    headers['Content-Type'] = 'application/json';
  }
  if (token) headers['Authorization'] = `Bearer ${token}`;
  headers['Auth'] = AUTH_TOKEN;
  headers['version'] = '1.0.0';
  return headers;
};

export interface ApiResponse<T = any> {
  success: boolean;
  data?: any;
  error?: string;
  code?: number;
  url?: string;
}

export function getStoredTokens() {
  const raw = storage.getString('@accessToken');
  if (!raw) return { accessToken: null };
  try {
    const state = JSON.parse(raw);
    return {
      accessToken: state ?? null,
    };
  } catch {
    return { accessToken: null };
  }
}

function getUserFriendlyError(error: any): string {
  if (error?.message?.includes('Network request failed')) {
    return 'No internet connection. Please check your network.';
  }
  if (error?.message?.includes('timeout')) {
    return 'The request timed out. Please try again.';
  }
  return 'An unexpected error occurred. Please try again.';
}

async function apiFetch<T>(
  path: string,
  options: RequestInit,
  retryCount = 0,
): Promise<ApiResponse<T>> {
  const maxRetries = 1;
  const baseURL = BASE_URL;
  const { accessToken } = getStoredTokens();
  const token = accessToken ?? undefined;

  try {
    const res = await fetch(`${baseURL}/${path}`, {
      ...options,
      headers: options.headers || headR(token, false),
    });

    const contentType = res.headers.get('content-type');
    const data = contentType?.includes('application/json')
      ? await res.json()
      : await res.text();

    if (res.status === 401 || res.status === 403) {
      DeviceEventEmitter.emit('session_expired');
    }

    return { success: res.ok, code: res.status, data, url: res.url };
  } catch (error: any) {
    console.log('EROR::', error);
    const message = getUserFriendlyError(error);

    if (
      retryCount < maxRetries &&
      (error.message?.includes('Network request failed') ||
        error.message?.includes('fetch') ||
        error.message?.includes('timeout'))
    ) {
      console.warn(`Retrying request (${retryCount + 1}) → ${path}`);
      await new Promise<void>(resolve => setTimeout(() => resolve(), 1000));
      return apiFetch<T>(path, options, retryCount + 1);
    }
    return { success: false, error: message, code: 0 };
  }
}

function buildUrl(path: string, params?: Record<string, any>): string {
  if (!params || Object.keys(params).length === 0) return path;
  const query = Object.entries(params)
    .filter(([_, v]) => v !== undefined && v !== null)
    .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
    .join('&');
  return `${path}?${query}`;
}

export const GETreq = <T>(path: string, queryParams?: Record<string, any>) => {
  const fullPath = buildUrl(path, queryParams);
  const { accessToken } = getStoredTokens();
  return apiFetch<T>(fullPath, {
    method: 'GET',
    headers: headR(accessToken ?? '', false),
  });
};

const isFilePart = (v: any): boolean =>
  !!v && typeof v === 'object' && typeof (v as any).uri === 'string';

const bodyHasFile = (body?: Record<string, any>): boolean =>
  !!body &&
  Object.values(body).some(v =>
    Array.isArray(v) ? v.some(isFilePart) : isFilePart(v),
  );

// Returns the request body plus the Content-Type it needs:
//  - has a file  -> multipart FormData (contentType undefined: fetch adds the boundary)
//  - simple form -> application/x-www-form-urlencoded (reliably transmits custom
//                   headers like `Auth` on React Native; multipart can drop them)
//  - non-form    -> JSON
const createPayload = (body?: Record<string, any>, isFormData = false) => {
  if (!body) {
    return { payload: undefined as any, contentType: undefined as string | undefined };
  }

  if (isFormData && bodyHasFile(body)) {
    const formData = new FormData();
    Object.entries(body).forEach(([key, value]) => {
      if (value === undefined || value === null) return;
      if (Array.isArray(value)) {
        value.forEach(item => {
          if (item !== undefined && item !== null) {
            formData.append(key, item as any);
          }
        });
      } else {
        formData.append(key, value as any);
      }
    });
    return { payload: formData, contentType: undefined as string | undefined };
  }

  if (isFormData) {
    const parts: string[] = [];
    const push = (key: string, v: any) =>
      parts.push(`${encodeURIComponent(key)}=${encodeURIComponent(String(v))}`);
    Object.entries(body).forEach(([key, value]) => {
      if (value === undefined || value === null) return;
      if (Array.isArray(value)) {
        value.forEach(item => {
          if (item !== undefined && item !== null) push(key, item);
        });
      } else {
        push(key, value);
      }
    });
    return {
      payload: parts.join('&'),
      contentType: 'application/x-www-form-urlencoded',
    };
  }

  return { payload: JSON.stringify(body), contentType: 'application/json' };
};

export const POSTreq = <T>(
  path: string,
  body?: Record<string, any>,
  isFormData = false,
) => {
  const { payload, contentType } = createPayload(body, isFormData);
  const { accessToken } = getStoredTokens();
  const headers = headR(accessToken ?? '', isFormData);
  if (contentType) headers['Content-Type'] = contentType;
  return apiFetch<T>(path, {
    method: 'POST',
    headers,
    body: payload,
  });
};

export const PATCHreq = <T>(path: string, body?: Record<string, any>) => {
  const { accessToken } = getStoredTokens();
  return apiFetch<T>(path, {
    method: 'PATCH',
    headers: headR(accessToken ?? ''),
    body: JSON.stringify(body),
  });
};

export const PUTreq = <T>(
  path: string,
  body?: Record<string, any>,
  isFormData = false,
) => {
  const { payload, contentType } = createPayload(body, isFormData);
  const { accessToken } = getStoredTokens();
  const headers = headR(accessToken ?? '', isFormData);
  if (contentType) headers['Content-Type'] = contentType;
  return apiFetch<T>(path, {
    method: 'PUT',
    headers,
    body: payload,
  });
};

export const DELETEreq = <T>(
  path: string,
  body?: Record<string, any>,
  isFormData = false,
) => {
  const { payload, contentType } = createPayload(body, isFormData);
  const { accessToken } = getStoredTokens();
  const headers = headR(accessToken ?? '', isFormData);
  if (contentType) headers['Content-Type'] = contentType;
  return apiFetch<T>(path, {
    method: 'DELETE',
    headers,
    body: payload,
  });
};

export const handleApiResponse = <T>(
  response: ApiResponse<T>,
  onSuccess?: (data: T) => void,
  onError?: (error: string) => void,
): boolean => {
  if (response.success && (response.code === 200 || response.code === 202)) {
    onSuccess?.(response.data as T);
    return true;
  }

  const message =
    typeof response.data === 'string'
      ? response.data
      : response.error || 'Something went wrong.';
  onError?.(message);
  return false;
};

export const checkNetworkConnectivity = async (): Promise<boolean> => {
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5000);
    const res = await fetch('https://www.google.com', {
      method: 'HEAD',
      signal: controller.signal,
    });
    clearTimeout(timeoutId);
    return res.ok;
  } catch {
    return false;
  }
};

export const POSTreqWithBlob = async (path: string, body?: any) => {
  const { accessToken } = getStoredTokens();
  return await ReactNativeBlobUtil.fetch(
    'POST',
    `${BASE_URL}/${path}`,
    {
      Authorization: `Bearer ${accessToken}`,
      Accept: 'application/json',
      'Content-Type': 'multipart/form-data',
      Auth: AUTH_TOKEN,
      version: '1.0.0',
    },
    body,
  );
  // if (response.info().status === 401 || response.info().status === 403) {
  //   DeviceEventEmitter.emit('session_expired');
  // }
  // return response;
};
