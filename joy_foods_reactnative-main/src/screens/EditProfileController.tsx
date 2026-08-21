import { BACK_BTN_IC, CAMERA_IC, CLOSE_LARGE_IC, EMAIL_IC, GALLERY_IC, TELEPHONE_IC, USER_IC } from 'assets';
import { BTN, DynamicInputRef, InputField, useLoader, useSnackbar } from 'components';
import BottomActionSheet from 'components/ui/BottomActionSheet';
import { useT } from 'internationalization';
import React, { useContext, useRef, useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ScrollView, Image, Pressable, KeyboardAvoidingView } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { StackProps } from 'types';
import { _COL, FONT, isIOS } from 'utils';
import ImagePicker, { Image as ImgType } from 'react-native-image-crop-picker';
import { compressImage, getFileObj } from 'function';
import { AppCtx } from 'store';
import { POSTreq, POSTreqWithBlob } from 'api';
import { SET_PROFILE_DATA } from 'store/context';
import ReactNativeBlobUtil from 'react-native-blob-util';

const EditProfileController = ({ navigation, }: StackProps<'EditProfileScr'>) => {
  const { dispatch, state: { profileData } } = useContext(AppCtx);
  const [firstName, setFirstName] = useState(profileData?.first_name || '');
  const [lastName, setLastName] = useState(profileData?.last_name || '');
  const [email, setEmail] = useState(profileData?.email || '');
  const [phoneNumber, setPhoneNumber] = useState(profileData?.phone || '');
  const { t } = useT();
  const { top } = useSafeAreaInsets();
  const [isCameraBtn, setIsCameraBtn] = useState(false);
  const firstNameRef = useRef<DynamicInputRef>(null);
  const lastNameRef = useRef<DynamicInputRef>(null);
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();
  const [profileImage, setProfileImage] = useState<{
    uri: string;
    obj: {
      uri: string;
      name: string;
      type: string;
    };
  }>();

  const handleCameraPress = () => {
    ImagePicker.openCamera({ cropping: false })
      .then(async image => {
        console.log("Camera Image::", JSON.stringify(image, null, 3));
        const fileObj = getFileObj(image);
        const compressedImage = await compressImage(fileObj?.uri);
        console.log('Compressed Image::', compressedImage);
        setProfileImage({
          ...fileObj,
          obj: { ...fileObj.obj, uri: compressedImage },
        });
        setIsCameraBtn(false);
      })
      .catch(error => {
        console.log(error);
      });
  };

  const handleGalleryPress = () => {
    ImagePicker.openPicker({ cropping: false })
      .then(async image => {
        console.log(image);
        const fileObj = getFileObj(image);
        const compressedImage = await compressImage(fileObj?.uri);
        console.log('Compressed Image::', compressedImage);
        setProfileImage({
          ...fileObj,
          obj: { ...fileObj.obj, uri: compressedImage },
        });
        setIsCameraBtn(false);
      })
      .catch(error => {
        console.log(error);
      });
  };

  const handleEditResponse = (data: any) => {
    dispatch({
      type: SET_PROFILE_DATA,
      profileData: {
        ...profileData,
        first_name: data?.data?.profile?.first_name,
        last_name: data?.data?.profile?.last_name,
        full_name: data?.data?.profile?.full_name,
        phone: data?.data?.profile?.phone,
        profile_picture: data?.data?.profile?.profile_picture,
      },
    });
    showSnackbar(data?.message || t('PROFILE_UPDATED_SUCCESS'));
    navigation.goBack();
  }

  const handleSavePress = async () => {
    let hasError = false;
    if (!firstName.trim()) {
      firstNameRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else {
      firstNameRef.current?.clearError();
    }
    if (!lastName.trim()) {
      lastNameRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else {
      lastNameRef.current?.clearError();
    }
    if (hasError) return;
    try {
      showLoader();
      if (isIOS) {
        const payload: any = {
          first_name: firstName,
          last_name: lastName,
          phone: phoneNumber,
        };
        if (profileImage) {
          payload.profile_picture = profileImage?.obj;
        }
        console.log('Edit Profile Payload::', JSON.stringify(payload, null, 3));
        const { success, data, error } = await POSTreq('profile/edit_profile', payload, true);
        console.log('Edit Profile Response::', JSON.stringify(data, null, 3));
        if (success) {
          handleEditResponse(data);
        } else {
          showSnackbar(error || data?.message);
        }
        hideLoader();
      }
      else {
        await POSTreqWithBlob('profile/edit_profile', [
          { name: 'first_name', data: firstName },
          { name: 'last_name', data: lastName },
          { name: 'phone', data: phoneNumber },
          {
            name: 'profile_picture',
            filename: profileImage?.obj?.name,
            type: profileImage?.obj?.type,
            data: ReactNativeBlobUtil.wrap(profileImage?.obj?.uri ?? ""),
          },
        ]).then(res => {
          console.log('Edit Profile Response::', JSON.stringify(res, null, 3));
          const data = JSON.parse(res.data);
          if (res.respInfo.status === 200) {
            handleEditResponse(data);
          } else {
            showSnackbar(data?.message);
          }
        }).catch(error => {
          console.log('Edit Profile Error::', error);
        }).finally(() => {
          hideLoader();
        })
      }
    } catch (error) {
      console.log('Edit Profile Error::', error);
    }
  };

  return (
    <View style={[styles.container, { paddingTop: top + 12 }]}>
      <View style={{ flexDirection: 'row' }}>
        <TouchableOpacity
          style={styles.backBtn}
          onPress={() => {
            navigation.goBack();
          }}
        >
          <BACK_BTN_IC />
        </TouchableOpacity>

        <Text allowFontScaling={false} style={styles.title}>{t('EDIT_PROFILE')}</Text>
      </View>
      <View style={styles.divider} />
      <KeyboardAvoidingView
        behavior={isIOS ? 'padding' : undefined}
        style={{ flex: 1 }}
      >
        <ScrollView showsVerticalScrollIndicator={false}>
          <View style={styles.profileImageSection}>
            <View>
              <Image
                source={{
                  uri: profileImage?.uri || profileData?.profile_picture,
                }}
                style={styles.profileImage}
              />
              <TouchableOpacity
                style={styles.cameraButton}
                onPress={() => setIsCameraBtn(!isCameraBtn)}
              >
                <CAMERA_IC />
              </TouchableOpacity>
            </View>
          </View>

          {/* Form Section */}
          <View style={styles.formContainer}>
            <InputField
              label={t('FIRST_NAME')}
              placeholder={t('ETR_FIRST_NAME')}
              value={firstName}
              onChangeText={setFirstName}
              leftIcon={<USER_IC />}
              containerStyle={{ marginTop: 30 }}
              ref={firstNameRef}
            />
            <InputField
              label={t('LAST_NAME')}
              placeholder={t('ETR_LAST_NAME')}
              value={lastName}
              onChangeText={setLastName}
              leftIcon={<USER_IC />}
              containerStyle={{ marginTop: 16 }}
              ref={lastNameRef}
            />

            <InputField
              label={t('EMAIL')}
              placeholder={t('PLACEHOLDER_EMAIL')}
              value={email}
              onChangeText={setEmail}
              leftIcon={<EMAIL_IC />}
              editable={false}
              containerStyle={{ marginTop: 16 }}
            />

            <InputField
              label={t('PHONE_NUMBER')}
              placeholder={'1234 567 890'}
              value={phoneNumber}
              onChangeText={setPhoneNumber}
              leftIcon={<TELEPHONE_IC />}
              containerStyle={{ marginTop: 16 }}
            />

            <BTN title={t('SAVE')} onP={handleSavePress} mTop={24} borderR={56} />
          </View>
        </ScrollView>
      </KeyboardAvoidingView>

      <BottomActionSheet
        visible={isCameraBtn}
        onClose={() => setIsCameraBtn(false)}
      >
        <View style={styles.bottomSheetContainer}>
          <View style={styles.headingContainer}>
            <Text allowFontScaling={false} style={styles.headingText}>{t('CHOOSE_OPTION')}</Text>
            <TouchableOpacity onPress={() => setIsCameraBtn(false)}>
              <CLOSE_LARGE_IC />
            </TouchableOpacity>
          </View>

          <View style={styles.optionContainer}>
            <Pressable style={{ flex: 1 }} onPress={handleCameraPress}>
              <CAMERA_IC height={48} width={48} style={styles.optionImage} />
              <Text allowFontScaling={false} style={styles.optionText}>{t('CAMERA')}</Text>
            </Pressable>
            <View style={styles.topicDivider} />
            <Pressable style={{ flex: 1 }} onPress={handleGalleryPress}>
              <GALLERY_IC height={48} width={48} style={styles.optionImage} />
              <Text allowFontScaling={false} style={styles.optionText}>{t('GALLERY')}</Text>
            </Pressable>
          </View>
        </View>
      </BottomActionSheet>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  backBtn: {
    position: 'absolute',
    left: 16,
    zIndex: 10,
    bottom: -2,
  },
  title: {
    fontFamily: FONT.SEMI_BOLD,
    fontSize: 20,
    textAlign: 'center',
    flex: 1,
    color: _COL.FINAL_BLACK,
    lineHeight: 24,
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER_FOURTH,
    marginTop: 12,
  },
  profileImageSection: {
    alignItems: 'center',
    marginTop: 32,
  },
  profileImage: {
    width: 110,
    height: 110,
    borderRadius: 55,
  },
  cameraButton: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    borderRadius: 18,
    backgroundColor: _COL.WHITE,
    borderWidth: 1,
    borderColor: _COL.PRIMARY_RED,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 7,
  },
  formContainer: {
    paddingHorizontal: 20,
  },
  input: {
    flex: 1,
    fontSize: 16,
    color: '#000000',
    padding: 0,
  },
  disabledInput: {
    backgroundColor: '#F9F9F9',
  },
  disabledText: {
    flex: 1,
    fontSize: 16,
    color: '#666666',
  },
  saveButton: {
    backgroundColor: '#C94A4A',
    borderRadius: 50,
    paddingVertical: 18,
    alignItems: 'center',
    marginTop: 12,
    shadowColor: '#C94A4A',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 8,
  },
  saveButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  bottomSheetContainer: {
    paddingBottom: 50,
  },
  headingContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 24,
    marginTop: 24,
  },
  headingText: {
    fontSize: 20,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
  },
  optionContainer: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginTop: 48,
    paddingHorizontal: 24,
  },
  topicDivider: {
    width: 1,
    backgroundColor: _COL.BORDER,
    marginHorizontal: 10,
  },
  optionText: {
    fontSize: 16,
    marginTop: 12,
    fontFamily: FONT.MEDIUM,
    color: _COL.MAIN_BLACK,
    alignSelf: 'center',
    textAlign: 'center',
  },
  optionImage: {
    alignSelf: 'center',
  },
});

export default EditProfileController;
