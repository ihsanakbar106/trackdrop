import { Toast, useAppBridge, useNavigate } from "@shopify/app-bridge-react";
import {
    Badge,
    Button,
    Card,
    ChoiceList,
    Icon,
    IndexFilters,
    InlineGrid,
    IndexTable,
    InlineStack,
    Layout,
    LegacyCard,
    LegacyStack,
    Link,
    Page,
    RangeSlider,
    Text,
    TextField,
    Tooltip,
    BlockStack,
    FormLayout,
    useBreakpoints,
    useIndexResourceState,
    useSetIndexFiltersMode,
    Thumbnail,
} from "@shopify/polaris";
import {
    CheckIcon
} from '@shopify/polaris-icons';
import { ExternalSmallIcon } from "@shopify/polaris-icons";
import React, { useCallback, useContext, useEffect, useState } from "react";
import { AppContext } from "../../components";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import { SkeltonPage, SkeltonPageForTable } from "../../components/SkeltonPage";
import TableSkeletonWithTabs from "../../components/TableSkeletonWithTabs";
import { useTranslation } from "react-i18next";

export default function Settings() {
    const { t } = useTranslation();
    const navigate = useNavigate();

    const { apiUrl, shop } = useContext(AppContext);
    const appBridge = useAppBridge();
    const [loading, setLoading] = useState(true);
    const [btnLoading, setBtnLoading] = useState(false);
    const [toastMsg, setToastMsg] = useState("");
    const [errorToast, setErrorToast] = useState(false);
    const [successToast, setSuccessToast] = useState(false);
    const [dropShippinigMode, setDropShippinigMode] = useState(false);
    const [trackingLink, setTrackingLink] = useState(false);
    const [dropShippinigKeyword, setDropShippinigKeyword] = useState('');
    const [translation, setTranslation] = useState({});
    const toggleDropShippinigMode = useCallback(() => setDropShippinigMode((dropShippinigMode) => !dropShippinigMode), []);
    const toggleTrackingLink = useCallback(() => setTrackingLink((trackingLink) => !trackingLink), []);

    const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
    const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

    const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

    const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

    const fetchData = async () => {
        try {
            const sessionToken = await getSessionToken(appBridge);
            const headers = {
                Authorization: `Bearer ${sessionToken}`,
            };
            const response = await axios.get(
                `${apiUrl}settings`, {headers},
            );
            const { plan_id,dropshipping_mode,dropshipping_keyword,tracking_link,translation} = response?.data;
            if(!plan_id){
                navigate("/billing");
            }
            setDropShippinigMode(dropshipping_mode);
            setTrackingLink(tracking_link);
            setDropShippinigKeyword(dropshipping_keyword);
            setTranslation(translation);

        } catch (error) {
            console.error("Error fetching data:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleChangeDropShippinigKeyword = useCallback(
        (newValue) => setDropShippinigKeyword(newValue),
        [],
    );
    const handleChangeTranslation = useCallback(
        (keyword,newValue) => {
            setTranslation((prevValues) => ({
                ...prevValues,
                [keyword]: newValue
            }));
        },
        []
    );
    const handleChangeDropShippinigMode = async () => {
        const dropshiping=!dropShippinigMode;
        setDropShippinigMode(dropshiping);
    }
    const handleChangeTrackingLink = async () => {
        const trackinglink=!trackingLink;
        setTrackingLink(trackinglink);
    }
    const handleSubmit = async () => {


        setBtnLoading(true);
        try {
            let sessionToken = await getSessionToken(appBridge);
            const payload = {
                dropshipping_mode: dropShippinigMode,
                tracking_link: trackingLink,
                dropshipping_keyword: dropShippinigKeyword,
                // translation: translation,
            };

            const response = await axios.post(`${apiUrl}save-settings`, payload, {
                headers: {
                    Authorization: `Bearer ${sessionToken}`,
                },
            });
            if (response?.data?.status === "success") {

                setSuccessToast(true);
                setToastMsg(response?.data?.message);
            }
            setBtnLoading(false);
        } catch (error) {
            setBtnLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);
    return loading ? (
        <TableSkeletonWithTabs
            primaryAction={false}
            length="5"
            fullWidth={false}
            SkeletonTabsLeft={false}
            thumbnail={false}
            checkbox={false}
            SkeletonTabsLeftLength={0}
        />
    ) : (
        <Page title={"Settings"}
              primaryAction={{
                  content: 'Save',
                  onAction: handleSubmit,
                  loading :btnLoading
              }}
        >

            <Layout>
                <Layout.Section>
                    <BlockStack gap={400}>
                        <InlineGrid columns={['oneHalf','twoThirds']}>
                            <BlockStack>
                                <Text variant="headingMd" as="h2">Drop Shipping mode</Text>
                            </BlockStack>
                            <Card sectioned>
                                <BlockStack gap={200}>
                                    <div>
                                <InlineStack align={"space-between"} blockAlign={"center"}>
                                    <InlineStack gap={100} align={"start"} blockAlign={"center"}>
                                        <Text variant="headingMd" as="h2">Drop Shipping mode features</Text>
                                        {dropShippinigMode?
                                            <Badge tone={"success"}>On</Badge>:
                                            <Badge>Off</Badge>}
                                    </InlineStack>
                                    <Button onClick={handleChangeDropShippinigMode} >{dropShippinigMode?"Turn off":"Turn on"}</Button>
                                </InlineStack>
                                <InlineStack align={"start"} blockAlign={"center"}>
                                    <div>
                                        <Icon
                                            source={CheckIcon}
                                            tone={dropShippinigMode?"success":"subdued"}
                                        /></div>
                                    <Text variant="bodyMd" as="p" tone={dropShippinigMode?"base":"subdued"}>Add some drop shipping carriers into your carrier matching rules.</Text>
                                </InlineStack>
                                <InlineStack align={"start"} blockAlign={"center"}>
                                    <div>
                                        <Icon
                                            source={CheckIcon}
                                            tone={dropShippinigMode?"success":"subdued"}
                                        /></div>
                                    <Text variant="bodyMd" as="p" tone={dropShippinigMode?"base":"subdued"}>Make the map show the destination address instead of the current address.</Text>
                                </InlineStack>
                                <InlineStack align={"start"} blockAlign={"center"}>
                                    <div>
                                        <Icon
                                            source={CheckIcon}
                                            tone={dropShippinigMode?"success":"subdued"}
                                        /></div>
                                    <Text variant="bodyMd" as="p" tone={dropShippinigMode?"base":"subdued"}>Add the keywords "China" and "aliexpress" into the blacklisted location.</Text>
                                </InlineStack>
                                    </div>
                                <TextField
                                    label="TRACK YOUR ORDER"
                                    value={dropShippinigKeyword}
                                    onChange={handleChangeDropShippinigKeyword}
                                    disabled={!dropShippinigMode}
                                    autoComplete="off"
                                    helpText="Add coma seprated keywords into the blacklisted location i.e China,aliexpress"
                                />
                                </BlockStack>
                            </Card>

                        </InlineGrid>
                        <InlineGrid columns={['oneHalf','twoThirds']}>
                            <BlockStack>
                                <Text variant="headingMd" as="h2">Tracking link</Text>
                            </BlockStack>
                            <Card sectioned>
                                <BlockStack gap={200}>
                                    <div>
                                <InlineStack align={"space-between"} blockAlign={"center"}>
                                    <InlineStack gap={100} align={"start"} blockAlign={"center"}>
                                        <Text variant="headingMd" as="h2">Update Shopify's native tracking links</Text>
                                        {trackingLink?
                                            <Badge tone={"success"}>On</Badge>:
                                            <Badge>Off</Badge>}
                                    </InlineStack>
                                    <Button onClick={handleChangeTrackingLink} >{trackingLink?"Turn off":"Turn on"}</Button>
                                </InlineStack>
                                <InlineStack align={"start"} blockAlign={"center"}>
                                    <Text variant="bodyMd" as="p" >Auto-update Shopify's native tracking links (which direct to carriers website) with your store's default tracking page URL..</Text>
                                </InlineStack>
                                    </div>
                                </BlockStack>
                            </Card>

                        </InlineGrid>
                        {/*<InlineGrid columns={['oneHalf','twoThirds']}>
                            <BlockStack>
                                <Text variant="headingMd" as="h2">Translation</Text>
                            </BlockStack>
                            <Card sectioned>
                                <BlockStack gap={200}>
                                    <TextField
                                        label="Track Your Order"
                                        value={translation?.track_your_order}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('track_your_order',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Order number"
                                        value={translation?.order_number}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('order_number',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Order number placeholder"
                                        value={translation?.order_number_placeholder}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('order_number_placeholder',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Order number error"
                                        value={translation?.order_number_error}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('order_number_error',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Tracking number"
                                        value={translation?.tracking_number}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('tracking_number',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Tracking number placehonder"
                                        value={translation?.tracking_number_placeholder}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('tracking_number_placeholder',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Tracking number error"
                                        value={translation?.tracking_number_error}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('tracking_number_error',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Email or Phone number"
                                        value={translation?.email_phone_number}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('email_phone_number',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Email or Phone number placeholder"
                                        value={translation?.email_phone_number_placeholder}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('email_phone_number_placeholder',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Email or Phone number error"
                                        value={translation?.email_phone_number_error}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('email_phone_number_error',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Track Button"
                                        value={translation?.track_btn}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('track_btn',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Order Status Text"
                                        value={translation?.order_status_text}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('order_status_text',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Carrier"
                                        value={translation?.carrier_title}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('carrier_title',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Product"
                                        value={translation?.product_title}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('product_title',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Tracking page not published!"
                                        value={translation?.page_not_publish}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('page_not_publish',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Package Contents"
                                        value={translation?.package_content}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('package_content',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Delivered"
                                        value={translation?.pb_delivered}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('pb_delivered',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Out for Delivery"
                                        value={translation?.pb_out_for_delivery}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('pb_out_for_delivery',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="In Transit"
                                        value={translation?.pb_in_transit}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('pb_in_transit',newValue)}
                                        autoComplete="off"
                                    />
                                    <TextField
                                        label="Ordered"
                                        value={translation?.pb_ordered}  // Assuming translation is an object with 'id'
                                        onChange={(newValue) => handleChangeTranslation('pb_ordered',newValue)}
                                        autoComplete="off"
                                    />
                                </BlockStack>

                            </Card>

                        </InlineGrid>*/}
                    </BlockStack>

                </Layout.Section>

            </Layout>
            {toastErrorMsg}
            {toastSuccessMsg}
        </Page>
    );
}
