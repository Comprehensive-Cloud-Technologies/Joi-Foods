import { StyleSheet } from "react-native";
import { _COL, _WIDTH, FONT, isIOS } from "utils";

const orderDetailsSty = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: _COL.WHITE,
    },
    backBtn: {
        position: 'absolute',
        left: 16,
        zIndex: 1,
    },
    title: {
        fontSize: 18,
        flex: 1,
        fontFamily: FONT.SEMI_BOLD,
        color: _COL.FINAL_BLACK,
        textAlign: 'center',
        top: isIOS ? 4 : 2,
    },
    row: {
        flexDirection: 'row',
        borderBottomWidth: 1,
        borderBottomColor: _COL.BORDER,
        paddingBottom: isIOS ? 18 : 12,
    },
    secondRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: 16,
    },
    itemID: {
        fontSize: 16,
        fontFamily: FONT.MEDIUM,
        color: _COL.BLACK,
    },
    pickup: {
        fontSize: 14,
        fontFamily: FONT.REGULAR,
        color: _COL.BLACK,
        marginLeft: 8,
    },
    pickupScheduleContainer: {
        marginTop: 20,
        borderBottomWidth: 1,
        borderBottomColor: _COL.BORDER_NINTH,
        paddingBottom: 16,
    },
    rowBetween: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    scheduleTitle: {
        fontSize: 14,
        fontFamily: FONT.SEMI_BOLD,
        color: _COL.MAIN_BLACK,
    },
    scheduleTimeText: {
        fontSize: 16,
        fontFamily: FONT.REGULAR,
        color: _COL.BLACK,
        marginTop: 2,
        lineHeight: 24,
    },
    scheduleTime: {
        fontFamily: FONT.BOLD,
    },
    deliveryStatus: {
        fontSize: 12,
        fontFamily: FONT.SEMI_BOLD,
        color: _COL.GREEN,
        paddingHorizontal: 14,
        paddingVertical: 5,
        borderRadius: 42,
    },
    pageTitles: {
        fontSize: 18,
        fontFamily: FONT.SEMI_BOLD,
        color: _COL.FINAL_BLACK,
    },
    outletAddressText: {
        fontSize: 14,
        fontFamily: FONT.REGULAR,
        color: _COL.TEXT_GREY,
        marginTop: 8,
    },
    firstRowBetween: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginTop: 12,
    },
    secondRowBetween: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    leftRow: {
        flexDirection: 'row',
    },
    billInfoSubtitle: {
        fontSize: 14,
        fontFamily: FONT.REGULAR,
        color: _COL.MAIN_BLACK,
    },
    billInfoPrice: {
        fontSize: 14,
        fontFamily: FONT.REGULAR,
        color: _COL.FINAL_BLACK,
    },
    secondDivider: {
        height: 1,
        backgroundColor: _COL.BORDER_NINTH,
        marginTop: 12,
    },
    couponText: {
        fontSize: 14,
        fontFamily: FONT.REGULAR,
        color: _COL.SECONDARY_GREEN,
    },
    walletText: {
        fontSize: 14,
        fontFamily: FONT.REGULAR,
        color: _COL.SECONDARY_ORANGE,
    },
    totalAmountPrice: {
        fontSize: 18,
        fontFamily: FONT.BOLD,
        color: _COL.PRIMARY_RED,
    },
    preMealScheduleTitle: {
        fontSize: 14,
        fontFamily: FONT.SEMI_BOLD,
        color: _COL.MAIN_BLACK,
    },
    preMealScheduleView: {
        backgroundColor: _COL.SECONDARY_GREEN_10,
        paddingHorizontal: 10,
        paddingVertical: 5,
        borderRadius: 42,
    },
    preMealScheduleText: {
        fontSize: 12,
        fontFamily: FONT.SEMI_BOLD,
        color: _COL.SECONDARY_GREEN,
        lineHeight: 18
    },
    timingText: {
        fontSize: 16,
        fontFamily: FONT.REGULAR,
        color: _COL.MAIN_BLACK,
        marginRight: 8,
    },
    timing: {
        fontSize: 16,
        fontFamily: FONT.BOLD,
        color: _COL.MAIN_BLACK,
    },
    preMealScheduleDaysContainer: {
        paddingVertical: 2,
        paddingHorizontal: 12,
        backgroundColor: _COL.TRACK_ONE,
        borderRadius: 34,
    },
    preMealScheduleDays: {
        fontSize: 14,
        fontFamily: FONT.REGULAR,
        color: _COL.FINAL_BLACK,
        lineHeight: 24
    },
    divider: {
        height: 1,
        backgroundColor: _COL.BORDER,
        marginVertical: 16
    },
    mealScheduleText: {
        fontSize: 18,
        fontFamily: FONT.SEMI_BOLD,
        color: _COL.FINAL_BLACK,
        marginBottom: 16
    },
    mealScheduleItemContainer: {
        borderBottomWidth: 1,
        borderColor: _COL.BORDER,
        paddingBottom: 14,
    },
    mealScheduleItemDay: {
        fontSize: 14,
        fontFamily: FONT.REGULAR,
        color: _COL.FINAL_BLACK,
    },
    mealScheduleItemPrice: {
        fontSize: 14,
        fontFamily: FONT.SEMI_BOLD,
        color: _COL.FINAL_BLACK,
    },
    mealScheduleItems: {
        fontSize: 12,
        fontFamily: FONT.REGULAR,
        color: _COL.TEXT_GREY,
        marginTop: 8,
        textAlign: 'left',
        width: _WIDTH - 100,
    },
    vegNonVegIcon: {
        position: 'absolute',
        right: 0,
        bottom: 0,
    },
    billInfoRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
    },
    mealScheduleStatus: {
        paddingHorizontal: 10,
        paddingVertical: 5,
        borderRadius: 42,
        marginLeft: 5,
    },
    mealScheduleStatusText: {
        fontSize: 12,
        fontFamily: FONT.SEMI_BOLD,
        lineHeight: 18
    },
    cancelHeading: {
        fontSize: 22,
        fontFamily: FONT.BOLD,
        color: _COL.FINAL_BLACK,
        textAlign: 'left',
        marginTop: 27,
        lineHeight: 30,
    },
    cancelDesc: {
        fontSize: 14,
        fontFamily: FONT.REGULAR,
        color: _COL.MAIN_BLACK,
        textAlign: 'left',
        marginTop: 10,
        lineHeight: 24,
    },
});

export default orderDetailsSty;