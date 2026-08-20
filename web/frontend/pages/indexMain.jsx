import {
    Badge,
    Button,
    EmptyState,
    IndexTable,
    Layout,
    LegacyCard,
    Page,
    Spinner,
    Text,
    Toast,
} from "@shopify/polaris";
import axios from "axios";
import { useCallback, useContext, useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { SkeltonPageForTable } from "../components/global/SkeltonPage";
import dateFormat from "dateformat";
import { getSessionToken } from "@shopify/app-bridge-utils";
import { useAppBridge } from "@shopify/app-bridge-react";
import { AppContext } from "../components";

export default function HomePage() {
    const navigate = useNavigate();
    const appBridge = useAppBridge();
    const {apiUrl} = useContext(AppContext);
    const [shopWidgetData, setShopWidgetData] = useState([]);
    const [toggleLoadData, setToggleLoadData] = useState(true);
    const [loading, setLoading] = useState(true);
    const [btnLoading, setBtnLoading] = useState(false);
    const [errorToast, setErrorToast] = useState(false);
    const [successToast, setSuccessToast] = useState(false);
    const [toastMsg, setToastMsg] = useState("");

    const toggleErrorMsgActive = useCallback(
        () => setErrorToast((errorToast) => !errorToast),
        []
    );
    const toggleSuccessMsgActive = useCallback(
        () => setSuccessToast((successToast) => !successToast),
        []
    );

    const toastErrorMsg = errorToast ? (
        <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} />
    ) : null;

    const toastSuccessMsg = successToast ? (
        <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} />
    ) : null;

    const fetchData = async () => {
        let sessionToken = await getSessionToken(appBridge);
        try {
            const response = await axios.get(`${apiUrl}shop-widgets`, {
                headers: {
                    Authorization: `Bearer ${sessionToken}`,
                },
            });
            if (response?.status == 200) {
                setToggleLoadData(false);
                setShopWidgetData(response?.data?.shop_widgets);
                setLoading(false);
                setBtnLoading(false);
            }
        } catch (error) {
            setLoading(false);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (toggleLoadData) {
            fetchData();
        }
    }, [toggleLoadData]);

    const handleCheckboxChangeIsWidgetActive = async (
        index,
        widgetId,
        value
    ) => {
        let sessionToken = await getSessionToken(appBridge);
        let enableValue = "";
        setBtnLoading((prev) => {
            let toggleId;
            if (prev[index]) {
                toggleId = { [index]: false };
            } else {
                toggleId = { [index]: true };
            }
            return { ...toggleId };
        });

        if (value == 0) {
            enableValue = 1;
        } else {
            enableValue = 0;
        }
        try {
            setToastMsg('')
            setSuccessToast(false);
            const response = await axios.post(
                `${apiUrl}active-widget/${widgetId}`,
                {
                    status: enableValue,
                },
                {
                    headers: {
                        Authorization: `Bearer ${sessionToken}`,
                    },
                }
            );

            if (response.data?.status == "success") {
                setToggleLoadData(true);
                setSuccessToast(true);
                setToastMsg(response?.data?.message);
            } else {
                setErrorToast(true);
                setToastMsg(response?.data?.message);
            }
        } catch (error) {
            setBtnLoading(false);
            console.error("Error updating widget status", error);
        }
    };

    const resourceName = {
        singular: "widget",
        plural: "widgets",
    };

    const emptyStateMarkup = (
        <EmptyState
            heading="You don't have any active widget"
            action={{ content: "Add Widget" }}
            image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
        >
            <p>
                You can use the Files section to upload images, videos, and
                other documents. This example shows the content with a centered
                layout and full width.
            </p>
        </EmptyState>
    );

    function convertNumberToBoolean(value) {
        let booleanValue;
        if (value === 1) {
            booleanValue = true;
        } else {
            booleanValue = false;
        }
        return booleanValue;
    }

    const rowMarkup =
        shopWidgetData?.length &&
        shopWidgetData?.map(
            ({ id, created_at, session_id, status, widget }, index) => {
                return (
                    <IndexTable.Row id={id} key={id} position={index}>
                        <IndexTable.Cell>
                            <Button
                                variant="plain"
                                onClick={() =>
                                    navigate(
                                        widget?.widget_type ==
                                            "product recommendation"
                                            ? "/product-recommendations"
                                            : widget?.widget_type ==
                                              "warranty and insurance options"
                                            ? "/warranty-and-insurance"
                                            : widget?.widget_type ==
                                              "address validation and autocompletion"
                                            ? "/address-validation-and-autocompletion"
                                            : widget?.widget_type ==
                                              "custom checkout fields"
                                            ? "/custom-checkout-fields"
                                            : widget?.widget_type ==
                                              "gift wrap and message options"
                                            ? "/gift-wrap-and-message"
                                            : widget?.widget_type ==
                                              "delivery date and time selection"
                                            ? "/delivery-date-and-time-selection"
                                            : widget?.widget_type ==
                                              "custom styling and branding"
                                            ? "/custom-styling-and-branding"
                                            : widget?.widget_type ==
                                              "age verification"
                                            ? "/age-verification"
                                            : ""
                                    )
                                }
                            >
                                <Text
                                    variant="bodyMd"
                                    fontWeight="bold"
                                    as="span"
                                >
                                    {widget?.name}
                                </Text>
                            </Button>
                        </IndexTable.Cell>
                        <IndexTable.Cell>
                            {btnLoading[index] ? (
                                <div className="toggleSpinner">
                                    <Spinner size="small" />
                                </div>
                            ) : (
                                <span>
                                    <input
                                        disabled={btnLoading[index]}
                                        id={`toggle-${id}`}
                                        type="checkbox"
                                        name="status"
                                        className="tgl tgl-light"
                                        checked={convertNumberToBoolean(status)}
                                        onChange={() =>
                                            handleCheckboxChangeIsWidgetActive(
                                                index,
                                                id,
                                                status
                                            )
                                        }
                                    />
                                    <label
                                        htmlFor={`toggle-${id}`}
                                        className="tgl-btn"
                                    ></label>
                                </span>
                            )}
                        </IndexTable.Cell>
                        <IndexTable.Cell>
                            {created_at !== null &&
                                dateFormat(created_at, "mmm dd, yyyy hh:MM")}
                        </IndexTable.Cell>
                    </IndexTable.Row>
                );
            }
        );
    return (
        <>
            {loading ? (
                <SkeltonPageForTable />
            ) : (
                <Page
                    title="Widgets"
                    primaryAction={
                        <Button
                            onClick={() => navigate("/addWidget")}
                            variant="primary"
                        >
                            Add Widget
                        </Button>
                    }
                >
                    <Layout>
                        <Layout.Section>
                            <LegacyCard>
                                <IndexTable
                                    resourceName={resourceName}
                                    itemCount={shopWidgetData?.length}
                                    selectable={false}
                                    emptyState={emptyStateMarkup}
                                    headings={[
                                        { title: "Name" },
                                        { title: "Status" },
                                        { title: "Date" },
                                    ]}
                                >
                                    {rowMarkup}
                                </IndexTable>
                            </LegacyCard>
                        </Layout.Section>
                    </Layout>
                    {toastErrorMsg}
                    {toastSuccessMsg}
                </Page>
            )}
        </>
    );
}
