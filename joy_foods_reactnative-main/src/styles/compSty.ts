import { FontS } from "function";
import { StyleSheet } from "react-native";
import { _COL, _HEIGHT, _W, _WIDTH, FONT, isIOS } from "utils";

const compSty = StyleSheet.create({
    /*--- Prompt ---*/
    "promptV": {
        width: '90%',
        borderRadius: FontS(7),
        borderTopLeftRadius: FontS(8),
        borderBottomRightRadius: FontS(8),
        backgroundColor: _COL.DARKER_RED,
        paddingVertical: FontS(15),
        paddingHorizontal: FontS(20),
        shadowColor: _COL.GREY,
        shadowOpacity: 0.5,
        shadowOffset: { width: 0, height: 2 },
        shadowRadius: 5,
        elevation: 15,
        opacity: 0.95,
        position: 'absolute',
        zIndex: 99999,
        marginTop: FontS(20),
        left: '5%',
        right: '5%',
        justifyContent: 'center',
        alignItems: 'center',
    },
    "promptTxt": {
        color: _COL.RED,
        fontFamily: FONT.MEDIUM,
        fontSize: FontS(14),
        textAlign: 'center',
    },
    /*--- IcBtn ---*/
    "icBtn": {
        paddingHorizontal: FontS(12),
        paddingVertical: FontS(8),
        alignItems: "center",
        justifyContent: "center",
        flexDirection: "row",
    },
    "icBtnTxt": {
        fontFamily: FONT.SEMI_BOLD,
        fontSize: FontS(14),
        color: _COL.TEXT_BLACK,
        textAlign: "center",
        paddingHorizontal: 5,
        includeFontPadding: false
    },
    "icBtnV": {
        alignItems: "center",
        justifyContent: "center",
        overflow: "hidden",
        backgroundColor: _COL.TRANSPARENT,
    },
    "icBtnVr": {
        alignItems: "center",
        justifyContent: "center",
        overflow: "hidden",
        backgroundColor: _COL.TRANSPARENT,
        borderRadius: 50
    },
    /*--- BTN ---*/
    "BtnV": {
        width: "100%",
        // alignItems: "center",
        justifyContent: "center",
        overflow: "hidden",
    },
    "BtnVr": {
        width: "100%",
        // alignItems: "center",
        justifyContent: "center",
        borderRadius: FontS(8),
        overflow: "hidden",
    },
    "Btn": {
        padding: FontS(15),
        paddingTop: FontS(14.5),
        paddingBottom: FontS(15.5),
        width: "100%",
        flexDirection: "row",
        alignItems: "center",
        justifyContent: "center",
    },
    "BtnTxt": {
        fontFamily: FONT.BOLD,
        fontSize: FontS(14),
        color: _COL.TEXT_BLACK,
        textAlign: "center",
        width: "100%",
        maxWidth: "100%",
        alignSelf: 'center',
        includeFontPadding: false
    },
    "lIcV": {
        top: 0,
        bottom: 0,
        left: _WIDTH * .05,
        position: "absolute",
        alignItems: "center",
        justifyContent: "center"
    },
    "rIcV": {
        top: 0,
        bottom: 0,
        right: _WIDTH * .05,
        position: "absolute",
        alignItems: "center",
        justifyContent: "center"
    },
    /*--- Confirmation Alert ---*/
    "confirmationAlertV": {
        width: '90%',
        backgroundColor: _COL.WHITE,
        borderRadius: FontS(15),
        alignItems: "center",
        justifyContent: "center",
        paddingHorizontal: "5%",
        paddingVertical: "10%"
    },
    "confirmationAlertTitle": {
        fontFamily: FONT.BOLD,
        fontSize: FontS(18),
        color: _COL.TEXT_BLACK,
        textAlign: 'center',
        paddingHorizontal: FontS(20),
    },
    "confirmationAlertMsg": {
        fontFamily: FONT.REGULAR,
        fontSize: FontS(14),
        color: _COL.TEXT_BLACK,
        textAlign: 'center',
        paddingVertical: FontS(15),
        paddingHorizontal: FontS(20),
    },
    "confirmationAlertClose": {
        position: "absolute",
        top: 10,
        right: 10,
    },
    /*--- TxtInput ---*/
    "msgTxt": {
        fontSize: FontS(14),
        fontFamily: FONT.MEDIUM,
        margin: 5,
        color: _COL.ERROR_RED
    },
    "_msgTxt": {
        fontSize: FontS(14),
        fontFamily: FONT.REGULAR,
        margin: 5,
        color: _COL.TEXT_BLACK,
        alignSelf: "flex-start"
    },
    "titleIc": {
        justifyContent: "center",
        alignItems: "center",
        backgroundColor: _COL.TRANSPARENT,
        borderRadius: FontS(10),
        paddingHorizontal: 8
    },
    "inputIcon": {
        alignItems: "center",
        justifyContent: "center",
        borderRadius: FontS(12),
        paddingVertical: FontS(15),
        paddingHorizontal: FontS(10),
    },
    "txtInput": {
        // width: "100%",
        flex: 1,
        color: _COL.TEXT_BLACK,
        paddingHorizontal: FontS(12),
        paddingVertical: FontS(15),
        fontSize: FontS(14),
        fontFamily: FONT.REGULAR,
        includeFontPadding: false
    },
    "dropDownInp": {
        fontFamily: FONT.MEDIUM,
        fontSize: FontS(14),
        color: _COL.BLACK,
        paddingHorizontal: FontS(14),
        width: "100%",
        paddingVertical: isIOS ? FontS(14) : FontS(20),
        includeFontPadding: false
    },
    "inputContainer": {
        flex: 0,
        borderWidth: 1,
        borderColor: _COL.INPUT_BORDER + 80,
        borderRadius: FontS(12),
        width: "100%",
        backgroundColor: _COL.INPUT_BG + 80,
        flexDirection: "row",
        alignItems: "center",
        justifyContent: "space-between",
        paddingEnd: "2%",
    },
    "titleTxt": {
        fontFamily: FONT.REGULAR,
        fontSize: FontS(14),
        lineHeight: FontS(22),
        textAlign: "center",
        color: _COL.TEXT_BLACK,
        paddingBottom: FontS(4)
        // paddingLeft:FontS(15),
    },
    /*--- Section Header ---*/
    "sectionHeaderTxt": {
        fontFamily: FONT.BOLD,
        fontSize: FontS(16),
        color: _COL.TEXT_BLACK
    },
    "sectionHeaderV": {
        marginTop: 20,
        width: "100%",
        marginBottom: 5,
        flexDirection: "row",
        alignItems: "center",
        justifyContent: "space-between"
    },
    /*--- Category Card ---*/
    "catCardImg": {
        width: _W * .14,
        height: _W * .14,
        borderRadius: _W * .14,
    },
    "catCardTxt": {
        fontFamily: FONT.REGULAR,
        fontSize: FontS(12),
        color: _COL.TEXT_BLACK,
        textAlign: "center",
        marginTop: FontS(10)
    },
    /*--- Product Card ---*/
    "prodCardV": {
        maxWidth: _W * .38,
        borderRadius: 8,
        aspectRatio: 4 / 7,
        backgroundColor: _COL.SECONDARY,
    },
    "prodCardImg": {
        maxWidth: _W * .38,
        height: (_W * .38) / 0.7272727273,
        aspectRatio: 8 / 11,
        borderTopRightRadius: 8,
        borderTopLeftRadius: 8,
    },
    "prodCardTxt": {
        fontFamily: FONT.REGULAR,
        fontSize: FontS(12),
        color: _COL.TEXT_BLACK,
        marginTop: FontS(10)
    },
    "prodCardPrice": {
        fontFamily: FONT.BOLD,
        fontSize: 12,
        color: _COL.TEXT_BLACK,
        marginTop: 5
    },
    "prodCardMRP": {
        fontFamily: FONT.MEDIUM,
        fontSize: 12,
        color: _COL.SECONDARY_DARK,
        marginTop: 5,
        marginStart: _W * .02,
        textDecorationLine: "line-through"
    },
    "prodCardLikeBtn": {
        position: "absolute",
        top: 0,
        right: 0
    },
    /*--- Country Options Sheet ---*/
    countrySearchInput: {
        flex: 1,
        paddingLeft: "2%",
        paddingVertical: 15,
        fontFamily: FONT.MEDIUM,
        color: _COL.BLACK,
        fontSize: FontS(14),
    },
    CountryOptionsItm: {
        paddingVertical: FontS(10),
        // borderBottomWidth: 1,
        // borderBottomColor: _COL.BORDER,
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: "3%"
    },
    countryTxt: {
        fontFamily: FONT.REGULAR,
        fontSize: FontS(12),
    
        color: _COL.TEXT_GREY,
    },
    countryCodeTxt: {
        fontFamily: FONT.SEMI_BOLD,
        fontSize: FontS(14),
        lineHeight: FontS(17.7),
        flexGrow: 0,
        color: _COL.TEXT_GREY,
        paddingHorizontal: "3%",
        width: "20%"
    },
    SearchContainer: {
        flexDirection: "row",
        alignItems: "center",
        borderRadius: 12,
        backgroundColor: _COL.INPUT_BG,
        marginBottom: "3%",
        width: "100%",
    },
    "lineSpacing": {
        height: FontS(1),
        marginVertical: _HEIGHT * 0.03,
        backgroundColor: _COL.INPUT_BG,
        flex: 1,
        width: "100%"
    },
    "lineSeparatorTxt": {
        fontFamily: FONT.REGULAR,
        marginHorizontal: "3%",
        fontSize: FontS(14),
        color: _COL.TEXT_GREY,
        textAlign: "center",
    },

    /*--- NavigationHeader ---*/
    "navHeaderV": {
        width: '100%',
        justifyContent: 'space-between',
        alignItems: 'center',
        flexDirection: 'row',
        paddingHorizontal: "5%",
    },
    "navTitle": {
        marginHorizontal: _WIDTH * .15,
        maxWidth: _WIDTH * .7,
        width: "100%",
        alignSelf: 'center',
        justifyContent: 'center',
        alignItems: 'center',
        ...StyleSheet.absoluteFillObject,
    },
    "navTitleTxt": {
        fontFamily: FONT.BOLD,
        fontSize: FontS(16),
        color: _COL.BLACK,
        width: "100%",
        textAlign: "center",
    },
    /*--- Search input ---*/
    "searchInpV": {
        borderRadius: 100,
        backgroundColor: _COL.BORDER + 33
    },
    "searchInpT": {
        paddingStart: 0,
        paddingVertical: FontS(13)
    },
    "searchInpL": {
        paddingVertical: 10,
        paddingStart: 13
    },
    /*--- Blocked Users ---*/
    "userItemContainer": {
        // marginVertical: FontS(4),
        borderBottomWidth: 1,
        borderBottomColor: _COL.INPUT_BG,
    },
    "userAvatar": {
        width: FontS(54),
        height: FontS(54),
        borderRadius: FontS(27),
        backgroundColor: _COL.INPUT_BG,
    },
    "userDisplayName": {
        fontFamily: FONT.SEMI_BOLD,
        fontSize: FontS(14),
        color: _COL.TEXT_BLACK,
        marginBottom: FontS(2),
    },
    "userUsername": {
        fontFamily: FONT.REGULAR,
        fontSize: FontS(12),
        color: _COL.TEXT_GREY,
    },
    backdrop: {
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        zIndex: 999,
      },
      dropdown: {
        position: 'absolute',
        backgroundColor: _COL.WHITE,
        borderRadius: FontS(8),
        paddingVertical: FontS(8),
        minWidth: FontS(150),
        shadowColor: _COL.BLACK,
        shadowOffset: {
          width: 0,
          height: 2,
        },
        shadowOpacity: 0.25,
        shadowRadius: 3.84,
        elevation: 8,
        zIndex: 1000,
      },
      option: {
        paddingVertical: FontS(12),
        paddingHorizontal: FontS(16),
      },
      optionText: {
        fontSize: FontS(16),
        color: _COL.TEXT_BLACK,
        fontWeight: '400',
      },
   
});

export default compSty;