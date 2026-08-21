import { View, Text, TouchableOpacity, StyleSheet, Dimensions, Image, FlatList } from 'react-native'
import React, { useState } from 'react'
import { useRoute } from '@react-navigation/native'
import { _COL, FONT } from 'utils/constants'
import { useT } from 'internationalization';
import InputField from 'components/ui/InputField'
import { useSafeAreaInsets } from 'react-native-safe-area-context'
import { BACK_BTN_IC, CART2_IC, NON_VEG_IC, SEARCH_IC, VEG_IC } from 'assets'


const height = Dimensions.get('window').height;
const width = Dimensions.get('window').width;

const PopularItemsList = ({ navigation }: any) => {

    const { t } = useT();
    const route = useRoute();
    const insets = useSafeAreaInsets();


    const [search, setSearch] = useState('')

    const popularItemsData = [
        {
            id: '1',
            name: 'Margherita',
            image: require('../assets/images/home/pizza.png'),
            isStock: false,
            isVeg: false,
            price: 120,
        },
        {
            id: '2',
            name: 'Pepperoni',
            image: require('../assets/images/home/pizza.png'),
            isStock: true,
            isVeg: false,
            price: 150,
        },
        {
            id: '3',
            name: 'BBQ Chicken',
            image: require('../assets/images/home/pizza.png'),
            isStock: false,
            isVeg: false,
            price: 180,
        },
        {
            id: '4',
            name: 'Veggie Delight',
            image: '',
            isStock: false,
            isVeg: true,
            price: 160,
        },
    ];

    const handelItemPress = ({ item }: { item: any }) => {
        navigation.navigate('ItemDetails', { id: item.id });
    }

    const renderItem = ({ item }: { item: any }) => {
        return (

            <TouchableOpacity
                onPress={() => handelItemPress({ item })}
                activeOpacity={0.8}
                style={styles.popularItemWrapper}>
                {item.image ? (
                    <Image source={item.image} style={styles.popularItemImage} />
                ) : (
                    <Image source={require('../assets/images/home/placeHolder.png')} style={styles.popularItemImage} />
                )}


                <Text allowFontScaling={false} style={styles.popularItemName}>{item.name}</Text>
                {!item.isStock ? (
                    <Text allowFontScaling={false} style={styles.outOfStockText}>{t('OUT_OF_STOCK')}</Text>
                ) :
                    (
                        <Text allowFontScaling={false} style={styles.inStock}>{t('IN_STOCK')}</Text>
                    )}

                <Text allowFontScaling={false} style={styles.popularItemPrice}>₹{item.price.toFixed(2)}</Text>

                {item.isVeg ? (
                    <VEG_IC style={styles.isVegIcon} />
                ) : (
                    <NON_VEG_IC style={styles.isVegIcon} />
                )}

                <TouchableOpacity
                    style={[styles.addToCartButton, { opacity: item.isStock ? 1 : 0.5 }]}
                    onPress={() => { }}
                    disabled={!item.isStock}
                >

                    <CART2_IC />
                </TouchableOpacity>


            </TouchableOpacity>

        );
    }



    return (
        <View style={[styles.container, { paddingTop: insets.top + 12 }]}>
            <View style={styles.row}>
                <TouchableOpacity style={styles.backBtn}
                    onPress={() => { navigation.goBack() }}>
                    <BACK_BTN_IC />
                </TouchableOpacity>

                <Text allowFontScaling={false} style={styles.title}>Popular Items</Text>

            </View>
            <View style={styles.divider} />

            <InputField

                placeholder={t('SEARCH')}
                leftIcon={<SEARCH_IC />}
                value={search}
                onChangeText={text => setSearch(text)}
                isGradient={false}
                inputContainerStyle={styles.inputContainer}
                containerStyle={styles.inputOuterContainer}
                inputStyle={styles.inputText}
                autoCapitalize="none"
            />

            <View style={styles.popularItemContainer}>
                {/* Popular Items FlatList GridView */}
                <FlatList
                    data={popularItemsData}
                    keyExtractor={(item) => item.id}
                    numColumns={2}
                    showsVerticalScrollIndicator={false}
                    contentContainerStyle={styles.popularItemContentContainer}
                    renderItem={renderItem}
                />
            </View>


        </View>
    )
}

export default PopularItemsList


const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: _COL.WHITE,
        paddingTop: height * 0.01,
    },
    backBtn: {
        position: 'absolute',
        left: 16,
        zIndex: 10,
    },
    row: {
        flexDirection: 'row',
    },
    title: {
        fontFamily: FONT.SEMI_BOLD,
        fontSize: 20,
        textAlign: 'center',
        flex: 1,
        color: _COL.FINAL_BLACK,
    },
    divider: {
        height: 1,
        backgroundColor: _COL.BORDER_FOURTH,
        marginTop: 16,
    },

    popularItemContainer: {
        flex: 1,

    },
    popularItemWrapper: {
        flex: 1,
        margin: 5,
        height: 266,
        borderRadius: 12,
        backgroundColor: _COL.WHITE,
        borderWidth: 1,
        borderColor: _COL.BORDER_FOURTH,
        justifyContent: 'center',
        alignItems: 'center',
    },
    popularItemImage: {
        width: '100%',
        height: 175,
        position: 'absolute',
        top: 0,
        borderTopLeftRadius: 12,
        borderTopRightRadius: 12,
        resizeMode: 'cover',
    },
    popularItemName: {
        position: 'absolute',
        top: '70%',
        left: '10%',
        fontSize: 14,
        fontFamily: FONT.MEDIUM,
        color: _COL.MAIN_BLACK,
    },
    outOfStockText: {
        position: 'absolute',
        top: '76%',
        left: '10%',
        marginTop: 4,
        fontSize: 10,
        fontFamily: FONT.MEDIUM,
        color: _COL.RED,
    },
    inStock: {
        position: 'absolute',
        top: '76%',
        left: '10%',
        marginTop: 4,
        fontSize: 10,
        fontFamily: FONT.MEDIUM,
        color: _COL.GREEN,
    },
    popularItemContentContainer: {
        paddingLeft: 16,
        paddingRight: 16,
        paddingBottom: 20,
        marginTop: 10,
    },
    popularItemPrice: {
        position: 'absolute',
        top: '85%',
        left: '10%',
        fontSize: 14,
        fontFamily: FONT.SEMI_BOLD,
        color: _COL.FINAL_BLACK,
    },
    isVegIcon: {
        position: 'absolute',
        top: '3%',
        right: '3%',
    },
    addToCartButton: {
        position: 'absolute',
        bottom: 12,
        right: 12,
        width: 32,
        height: 32,
        borderRadius: 16,
        backgroundColor: _COL.SECONDARY_ORANGE,
        justifyContent: 'center',
        alignItems: 'center',
    },
    inputOuterContainer: {
        paddingHorizontal: 16,
        paddingTop: 16,
    },
    inputContainer: {
        backgroundColor: _COL.WHITE,
        borderWidth: 1,
        borderColor: _COL.BORDER_FIFTH,
        borderRadius: 12,
        height: height * 0.06,
    },
    inputText: {
        fontSize: 14,
        fontFamily: FONT.REGULAR,
        color: _COL.BLACK,
    },
});