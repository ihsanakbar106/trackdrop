import React, { useCallback, useContext, useEffect, useRef, useState } from "react";
import { AppContext } from "../../../components";
import { useLocation } from "react-router-dom";
import { useAppBridge, useNavigate } from "@shopify/app-bridge-react";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import {
  ActionList,
  BlockStack,
  Box,
  Button,
  ButtonGroup,
  Card,
  Icon,
  InlineCode,
  Layout,
  Page,
  Popover,
  SkeletonBodyText,
  SkeletonDisplayText,
  Spinner,
  Text,
  TextField,
  Toast,
} from "@shopify/polaris";
import { MenuVerticalIcon, PhoneIcon, SendIcon } from "@shopify/polaris-icons";

function convertNumberToBoolean(value) {
  let booleanValue;
  if (value === 1) {
    booleanValue = true;
  } else {
    booleanValue = false;
  }
  return booleanValue;
}

export default function SMS() {
  const navigate = useNavigate();
  const appBridge = useAppBridge();
  const emailSubjectDivRef = useRef(null);
  const location = useLocation();
  const ParamsId = location?.pathname?.split("/").pop();
  const { apiUrl } = useContext(AppContext);

  // Loading states
  const [loading, setLoading] = useState(true);
  const [btnLoading, setBtnLoading] = useState(false);

  const customizedataInitialList = {
    senderName: "",
    message: "",
  };

  const [customizedata, setCustomizeData] = useState(customizedataInitialList);

  // Email and notification states
  const [smsData, setSMSData] = useState("");
  const [activeStatus, setActiveStatus] = useState("");
  const [notificationType, setNotificationType] = useState("");
  const [popoverActiveVariable, setPopoverActiveVariable] = useState(false);

  // Toast notifications
  const [errorToast, setErrorToast] = useState(false);
  const [successToast, setSuccessToast] = useState(false);
  const [toastMsg, setToastMsg] = useState("");

  const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
  const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

  const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

  const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}sms-notification-detail/2`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { sms_notification_data } = response?.data;
      const data = JSON.parse(sms_notification_data?.data) || "";
      setSMSData(sms_notification_data);
      setCustomizeData(data || customizedataInitialList);
      setActiveStatus(sms_notification_data?.active_status);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, [ParamsId]);

  const togglePopoverActiveVariable = useCallback(() => setPopoverActiveVariable((popoverActiveVariable) => !popoverActiveVariable), []);

  const handleChangeValue = useCallback((field, value) => {
    setCustomizeData((prevState) => ({
      ...prevState,
      [field]: value,
    }));
  }, []);

  const insertVariable = (variable) => {
    const textField = emailSubjectDivRef.current.querySelector("textarea");
    if (!textField) return;

    const { selectionStart, selectionEnd, value } = textField;
    const newValue = `${value.substring(0, selectionStart)}${variable}${value.substring(selectionEnd)}`;
    handleChangeValue("message", newValue);
    setPopoverActiveVariable(false);
  };

  const handleCheckboxChangeIsActive = (currentStatus) => {
    setActiveStatus(currentStatus == 0 ? 1 : 0);
  };

  const handleSave = async (btnLoading) => {
    setBtnLoading((prev) => {
      let toggleId;
      if (prev[btnLoading]) {
        toggleId = { [btnLoading]: false };
      } else {
        toggleId = { [btnLoading]: true };
      }
      return { ...toggleId };
    });
    try {
      let sessionToken = await getSessionToken(appBridge);
      const payload = {
        data: customizedata,
        active_status: activeStatus,
      };

      const response = await axios.post(`${apiUrl}sms-notification-save/${ParamsId}`, payload, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      setBtnLoading(false);
      setSuccessToast(true);
      setToastMsg(response?.data?.message);
    } catch (error) {
      setBtnLoading(false);
    }
  };

  return (
    <Page
      backAction={{
        content: "Products",
        onAction: () => navigate(`/notifications/${smsData?.notification_id}`),
      }}
      title={smsData?.title}
      primaryAction={{
        content: "Save",
        loading: btnLoading["Save"],
        onAction: () => handleSave("Save"),
      }}
      secondaryActions={[
        {
          content: loading ? (
            <Spinner size="small" />
          ) : (
            <span className="flex items-center gap-2">
              <input
                id={`toggle-${ParamsId}`}
                type="checkbox"
                name="status"
                className="tgl tgl-light"
                checked={convertNumberToBoolean(activeStatus)}
                onChange={() => handleCheckboxChangeIsActive(activeStatus)}
              />
              <label htmlFor={`toggle-${ParamsId}`} className="tgl-btn"></label>
              <Text>Active/Inactive</Text>
            </span>
          ),
        },
      ]}
    >
      <div className="flex items-start pb-4">
        <div className="w-full">
          <Box padding="200">
            <BlockStack gap={"400"}>
              <Card>
                {loading ? (
                  <BlockStack gap={"200"}>
                    <SkeletonDisplayText size="small" />
                    <SkeletonBodyText lines={2} />
                  </BlockStack>
                ) : (
                  <BlockStack gap="100">
                    <Text variant="headingMd" as="h2" fontWeight="semibold">
                      Sender name
                    </Text>
                    <TextField
                      value={customizedata?.senderName}
                      onChange={(value) => handleChangeValue("senderName", value)}
                      autoComplete="off"
                      helpText="You can set the sender name here."
                    />
                  </BlockStack>
                )}
              </Card>
              <Box borderRadius="300" shadow="200" background="bg-surface" padding={"400"} minHeight="100%">
                {loading ? (
                  <BlockStack gap={"200"}>
                    <SkeletonDisplayText size="small" />
                    <SkeletonBodyText lines={15} />
                  </BlockStack>
                ) : (
                  <BlockStack gap="200">
                    <div ref={emailSubjectDivRef}>
                      <TextField
                        label={
                          <Text variant="headingSm" as="h2">
                            Message
                          </Text>
                        }
                        labelAction={{
                          content: (
                            <Popover
                              active={popoverActiveVariable}
                              activator={
                                <Button onClick={togglePopoverActiveVariable} variant="plain" size="medium" textAlign="center" disclosure>
                                  Insert variables
                                </Button>
                              }
                              autofocusTarget="first-node"
                              onClose={togglePopoverActiveVariable}
                            >
                              <Popover.Pane>
                                <ActionList
                                  actionRole="menuitem"
                                  items={[
                                    {
                                      content: "product title",
                                      onAction: () => insertVariable("{{product_title}}"),
                                    },
                                    {
                                      content: "store name",
                                      onAction: () => insertVariable("{{store_name}}"),
                                    },
                                    {
                                      content: "tracking number",
                                      onAction: () => insertVariable("{{tracking_number}}"),
                                    },
                                    {
                                      content: "carrier name",
                                      onAction: () => insertVariable("{{carrier_name}}"),
                                    },
                                    {
                                      content: "carrier contact",
                                      onAction: () => insertVariable("{{carrier_contact}}"),
                                    },
                                    {
                                      content: "dest. carrier name",
                                      onAction: () => insertVariable("{{dest_carrier_name}}"),
                                    },
                                    {
                                      content: "dest. contact NO",
                                      onAction: () => insertVariable("{{dest_contact_no}}"),
                                    },
                                    {
                                      content: "shipment status",
                                      onAction: () => insertVariable("{{shipment_status}}"),
                                    },
                                    {
                                      content: "latest tracking info",
                                      onAction: () => insertVariable("{{latest_tracking_info}}"),
                                    },
                                    {
                                      content: "latest update time",
                                      onAction: () => insertVariable("{{latest_update_time}}"),
                                    },
                                    {
                                      content: "tracking link",
                                      onAction: () => insertVariable("{{tracking_link}}"),
                                    },
                                    {
                                      content: "transit time",
                                      onAction: () => insertVariable("{{transit_time}}"),
                                    },
                                    {
                                      content: "customer first name",
                                      onAction: () => insertVariable("{{customer_first_name}}"),
                                    },
                                    {
                                      content: "customer full name",
                                      onAction: () => insertVariable("{{customer_full_name}}"),
                                    },
                                    {
                                      content: "order ID",
                                      onAction: () => insertVariable("{{order_id}}"),
                                    },
                                    {
                                      content: "comment",
                                      onAction: () => insertVariable("{{comment}}"),
                                    },
                                    {
                                      content: "estimated delivery date",
                                      onAction: () => insertVariable("{{estimated_delivery_date}}"),
                                    },
                                  ]}
                                />
                              </Popover.Pane>
                            </Popover>
                          ),
                        }}
                        value={customizedata?.message}
                        onChange={(value) => handleChangeValue("message", value)}
                        multiline={14}
                        autoComplete="off"
                      />
                    </div>
                  </BlockStack>
                )}
              </Box>
            </BlockStack>
          </Box>
        </div>
        <div className="sticky top-6">
          <div className="ml-4 hidden w-60 flex-none flex-col md:flex lg:ml-4 py-2">
            <div className="relative flex h-[480px] w-full flex-col overflow-hidden rounded-[32px] border-8 border-stone-950 bg-white">
              <div className="absolute left-1/2 top-0 z-10 h-4 w-28 -translate-x-1/2 rounded-b-xl bg-stone-950">
                <div className="absolute left-1/2 top-1 h-1 w-7 -translate-x-1/2 rounded-full  bg-stone-500"></div>
                <div className="absolute right-6 top-0 h-2.5 w-2.5 rounded-full border-2 border-stone-800 bg-stone-900"></div>
              </div>
              <div className="sticky left-0 top-0 flex h-14 w-full items-center justify-between truncate border-b border-stone-200 bg-stone-100 px-2 pt-4">
                <Text variant="bodyMd" fontWeight="medium">
                  {customizedata?.senderName || "SMS"}
                </Text>
                <ButtonGroup>
                  <Button disabled variant="tertiary" size="medium" textAlign="center" icon={PhoneIcon}></Button>
                  <Button disabled variant="tertiary" size="medium" textAlign="center" icon={MenuVerticalIcon}></Button>
                </ButtonGroup>
              </div>
              <div className="flex h-full flex-col overflow-y-auto scrollbar scrollbar-thumb-gray-900 scrollbar-track-gray-100">
                <div className="w-full px-2 py-3 text-center text-xs text-stone-500">Message Preview</div>
                {loading ? (
                  <Box padding={"200"}>
                    <SkeletonBodyText />
                  </Box>
                ) : (
                  <div className="border-3 mx-2 w-fit flex-none break-all rounded-r-xl rounded-t-xl bg-stone-200  p-3 text-xs">
                    <div dangerouslySetInnerHTML={{ __html: customizedata?.message }} />
                  </div>
                )}
              </div>
              <div className="absolute bottom-0 w-full bg-gradient-to-t from-white">
                <Button fullWidth icon={<Icon source={SendIcon} />}>
                  Send Test Message
                </Button>
              </div>
            </div>
            <div class="pt-6"></div>
          </div>
        </div>
      </div>
      {toastSuccessMsg}
      {toastErrorMsg}
    </Page>
  );
}
