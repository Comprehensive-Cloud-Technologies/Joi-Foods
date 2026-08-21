import { ACTIVE_RIGHT_IC, CROSS_IC, RIGHT_IC_STATUS } from 'assets';
import { tT, useT } from 'internationalization';
import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import Svg, { Line } from 'react-native-svg';
import { _COL, _W, FONT } from 'utils';

type StatusItem = {
  code?: string;
  text?: string;
  is_completed?: boolean;
  is_current?: boolean;
};

const OrderStatus = ({ status, statuses = [] }: { status: string; statuses?: StatusItem[] }) => {
  const { t } = useT();

  const isCancelled = status === 'CANCELLED' || status === 'REJECTED';

  // Use statuses from API if available, otherwise fallback to old logic
  const useApiStatuses = statuses.length > 0 && !isCancelled;

  const statusMap: { [key: string]: number } = {
    PENDING: 0,
    CONFIRMED: 1,
    CANCELLED: 1,
    REJECTED: 1,
    READY: 2,
    COMPLETED: 3,
  };

  const currentStatus = statusMap[status] ?? 0;

  const getStatusColor = (isCompleted: boolean) =>
    isCompleted ? _COL.SECONDARY_GREEN : _COL.TEXT_GREY_LIGHT;

  const getDashedLineColor = (isCompleted: boolean) =>
    isCompleted ? _COL.FINAL_BLACK : _COL.TEXT_GREY_LIGHT;

  const renderDashedLine = (isCompleted: boolean, width = 100) => (
    <View style={styles.dashedLineWrapper}>
      <Svg height="2" width="100%" viewBox={`0 0 ${width} 2`}>
        <Line
          x1="0"
          y1="1"
          x2={width}
          y2="1"
          stroke={getDashedLineColor(isCompleted)}
          strokeWidth="1.5"
          strokeDasharray="4,4"
        />
      </Svg>
    </View>
  );

  const renderStatusStep = (isCompleted: boolean, label: string) => (
    <View style={styles.statusColumn} key={label}>
      <View
        style={[styles.checkmarkCircle, { borderColor: getStatusColor(isCompleted) }]}
      >
        {isCompleted ? <ACTIVE_RIGHT_IC /> : <RIGHT_IC_STATUS />}
      </View>
      <Text allowFontScaling={false} style={styles.statusLabel} numberOfLines={1}>
        {label}
      </Text>
    </View>
  );

  if (useApiStatuses) {
    return (
      <View style={styles.statusWrapper}>
        <View style={styles.statusContainer}>
          {statuses.map((statusItem, index) => (
            <React.Fragment key={statusItem.code}>
              {renderStatusStep(statusItem.is_completed ?? false, statusItem.text ?? '')}
              {index < statuses.length - 1 && renderDashedLine(statusItem.is_completed ?? false)}
            </React.Fragment>
          ))}
        </View>
      </View>
    );
  }

  // Fallback to old logic for cancelled/rejected orders
  const getOldStatusColor = (statusId: number) =>
    statusId <= currentStatus ? _COL.SECONDARY_GREEN : _COL.TEXT_GREY_LIGHT;

  const getOldDashedLineColor = (statusId: number) =>
    statusId <= currentStatus ? _COL.FINAL_BLACK : _COL.TEXT_GREY_LIGHT;

  const renderOldDashedLine = (statusId: number, width = 100, isCancelled = false) => (
    <View style={styles.dashedLineWrapper}>
      <Svg height="2" width="100%" viewBox={`0 0 ${width} 2`}>
        <Line
          x1="0"
          y1="1"
          x2={width}
          y2="1"
          stroke={isCancelled ? "#FF0000" : getOldDashedLineColor(statusId)}
          strokeWidth="1.5"
          strokeDasharray="4,4"
        />
      </Svg>
    </View>
  );

  const renderOldStatusStep = (stepStatus: number, label: tT, isCancelled = false) => (
    <View style={styles.statusColumn} key={label}>
      <View
        style={[styles.checkmarkCircle, { borderColor: getOldStatusColor(stepStatus) },
        isCancelled && {
          paddingVertical: 5,
          paddingLeft: 4,
          paddingRight: 5,
          borderColor: "#FF0000"
        }
        ]}
      >
        {isCancelled ? <CROSS_IC width={16} height={14} /> : (stepStatus <= currentStatus ? <ACTIVE_RIGHT_IC /> : <RIGHT_IC_STATUS />)}
      </View>
      <Text allowFontScaling={false} style={styles.statusLabel} numberOfLines={1}>
        {t(label)}
      </Text>
    </View>
  );

  return (
    <View style={styles.statusWrapper}>
      <View style={styles.statusContainer}>
        {/* Placed */}
        {renderOldStatusStep(0, 'PLACED')}

        {!isCancelled ? (
          <>
            {renderOldDashedLine(1)}

            {renderOldStatusStep(1, 'APPROVED')}

            {renderOldDashedLine(2)}

            {renderOldStatusStep(2, 'PREPARED_OUT')}

            {renderOldDashedLine(3)}

            {/* Delivered */}
            {renderOldStatusStep(3, 'DELIVERED')}
          </>
        ) : (
          <>
            {/* Line to Cancelled */}
            {renderOldDashedLine(1, _W, true)}

            {/* Cancelled */}
            {renderOldStatusStep(1, status === 'REJECTED' ? 'REJECTED' : 'CANCELLED', true)}
          </>
        )}
      </View>
    </View>
  );
};

export default OrderStatus;

const styles = StyleSheet.create({
  statusWrapper: {
    paddingVertical: 16,
    paddingBottom: 14,
    paddingHorizontal: 35,
    backgroundColor: _COL.LIGHT_BG,
  },
  statusContainer: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
  },
  statusColumn: {
    alignItems: 'center',
    minWidth: 50,
  },
  dashedLineWrapper: {
    flex: 1,
    height: 2,
    alignSelf: 'flex-start',
    marginTop: 12,
    marginHorizontal: -10,
  },
  checkmarkCircle: {
    borderRadius: 50,
    borderWidth: 1.5,
    paddingVertical: 8,
    paddingLeft: 6,
    paddingRight: 7,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: _COL.WHITE,
    marginBottom: 9,
  },
  statusLabel: {
    fontSize: 10,
    fontFamily: FONT.REGULAR,
    color: _COL.BLACK,
    textAlign: 'center',
    // position: 'absolute',
    // bottom: -10,
  },
});