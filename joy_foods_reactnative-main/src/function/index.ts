import { PixelRatio } from 'react-native';
import { Image, Video } from 'react-native-compressor';
import { ImageOrVideo } from 'react-native-image-crop-picker';
import { _H, _W } from 'utils';

const fontScale = PixelRatio.getFontScale();
const pxS = (size: number) => size * (PixelRatio.get() / (_H / _W));
// const FontS = (size: number) => size / fontScale;
const FontS = (size: number) => size;

function toUri(path: string) {
  const uri = path?.includes('file://') ? path : 'file://' + path;
  return uri;
}

function getSortLetter(f: string, s: string) {
  const firstChar = ([...f][0]?.slice(0, 2) || f?.charAt(0))?.toUpperCase();
  const secondChar = ([...s][0]?.slice(0, 2) || s?.charAt(0))?.toUpperCase();
  return firstChar + secondChar;
}

const formatDuration = (seconds?: number): string => {
  if (!seconds) return '0:00';
  const mins = Math.floor(seconds / 60);
  const secs = Math.floor(seconds % 60);
  return `${mins}:${secs.toString().padStart(2, '0')}`;
};

async function compressImage(url: string): Promise<string> {
  return await Image.compress(url);
}

async function compressVideo(url: string): Promise<string> {
  try {
    const result = await Video.compress(url, { compressionMethod: "manual", bitrate: 300000 });
    // const result = await Video.compress(url, { compressionMethod: "auto" });
    return result;
  } catch (err) {
    console.log("Compression function ERROR::", JSON.stringify(err, null, 3));
    return "";
  }
}

const formatDate = (dateString: string) => {
  if (!dateString) return '';

  const date = new Date(dateString);

  return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }).replace(',', '');
};

function getFileObj(i: ImageOrVideo) {
  const pathFilename = i?.path?.split('/')?.pop();
  const randomNo = Math?.floor(Math?.random() * 1000000000000000)?.toString();
  const randomImgName = `${Date.now()}_${randomNo}.jpg`;
  const imgFileName = pathFilename || randomImgName;
  const uri = i?.path
    ? i?.path?.includes('file://')
      ? i.path
      : `file://${i.path}`
    : i?.sourceURL || i?.path;
  return {
    uri,
    obj: {
      uri,
      name: imgFileName,
      type: i?.mime,
    },
  };
}

const decrementFormattedAmount = (value: string, decrement = 10, isAdd = false) => {
  const currency = value.replace(/[\d.,\s]/g, '');

  const numericValue = parseFloat(value.replace(/[^\d.]/g, ''));

  const updatedValue = isAdd ? numericValue + decrement : numericValue - decrement;

  return `${currency}${updatedValue.toFixed(2)}`;
};

const formatTimeWithAmPm = (time: string): string => {
  if (!time) return "";

  const [hourStr, minute] = time.split(":");
  let hour = parseInt(hourStr, 10);

  const isPM = hour >= 12;
  const period = isPM ? "PM" : "AM";

  hour = hour % 12;
  hour = hour === 0 ? 12 : hour;

  return `${hour}:${minute} ${period}`;
};

const decodeHtml = (text: string) => {
  return text
    .replace(/&amp;/g, "&")
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">")
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'");
};


export {
  FontS, toUri, getSortLetter, formatDuration, compressImage, compressVideo, formatDate, getFileObj,
  decrementFormattedAmount, formatTimeWithAmPm, decodeHtml
};
