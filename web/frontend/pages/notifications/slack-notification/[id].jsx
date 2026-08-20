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
  FullscreenBar,
  Icon,
  InlineCode,
  InlineGrid,
  InlineStack,
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
  const [slackData, setSlackData] = useState("");
  const [emailsList, setEmailsList] = useState([]);
  const [activeStatus, setActiveStatus] = useState("");
  const [notificationType, setNotificationType] = useState("");
  const [popoverActive, setPopoverActive] = useState(false);
  const [popoverActiveVariable, setPopoverActiveVariable] = useState(false);

  console.log("popoverActive", popoverActive)

  // Toast notifications
  const [errorToast, setErrorToast] = useState(false);
  const [successToast, setSuccessToast] = useState(false);
  const [toastMsg, setToastMsg] = useState("");

  const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
  const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

  const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

  const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

  const fetchEmailList = async (id) => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}notification-detail/${id}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { emails, web_notifications, sms_notifications, slack_notifications,  notification_type } = response?.data;
      setEmailsList(emails || web_notifications || sms_notifications || slack_notifications || []);
      setNotificationType(notification_type || "");
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
    }
  };

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}slack-notification-detail/${ParamsId}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { slack_notification_data } = response?.data;
      const data = JSON.parse(slack_notification_data?.data) || "";
      setSlackData(slack_notification_data);
      setCustomizeData(data || customizedataInitialList);
      setActiveStatus(slack_notification_data?.active_status);
      fetchEmailList(slack_notification_data?.notification_id);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, [ParamsId]);

  const togglePopoverActive = useCallback(() => setPopoverActive((popoverActive) => !popoverActive), []);

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

      const response = await axios.post(`${apiUrl}slack-notification-save/${ParamsId}`, payload, {
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
    <>
      <div style={{ minHeight: "80vh" }}>
        <FullscreenBar onAction={() => navigate(`/notifications/${slackData?.notification_id}`)}>
          <Box paddingBlockStart={"300"} paddingBlockEnd={"300"} paddingInlineStart={"400"} paddingInlineEnd={"400"} width="100%">
            <InlineGrid columns="1fr 1fr auto">
              {loading ? (
                <>
                  <InlineStack blockAlign="center" wrap={false} gap={"400"}>
                    <SkeletonBodyText lines={1} />
                  </InlineStack>
                  <InlineStack></InlineStack>
                </>
              ) : (
                <>
                  <InlineStack blockAlign="center" wrap={false} gap={"400"}>
                    <Text variant="headingMd">{slackData?.title}</Text>
                  </InlineStack>
                  <InlineStack blockAlign="center" wrap={false} gap={"400"}>
                    <Popover
                      active={popoverActive}
                      activator={
                        <Button onClick={togglePopoverActive} variant="tertiary" size="medium" textAlign="center" disclosure>
                          <Text as="span" variant="bodySm" fontWeight="medium">
                            {slackData?.title}
                          </Text>
                        </Button>
                      }
                      autofocusTarget="first-node"
                      onClose={togglePopoverActive}
                    >
                      <ActionList
                        actionRole="menuitem"
                        items={emailsList?.map((item) => ({
                          content: item?.title,
                          onAction: () => {
                            setLoading(true);
                            navigate(`/notifications/slack-notification/${item?.id}`);
                            setPopoverActive((popoverActive) => !popoverActive);
                          },
                        }))}
                      />
                    </Popover>
                  </InlineStack>
                </>
              )}
              <InlineStack blockAlign="center" gap={"200"}>
                <span>
                  <input
                    id={`toggle-${activeStatus}`}
                    type="checkbox"
                    name="status"
                    className="tgl tgl-light"
                    checked={convertNumberToBoolean(activeStatus)}
                    onChange={() => handleCheckboxChangeIsActive(activeStatus)}
                  />
                  <label htmlFor={`toggle-${activeStatus}`} className="tgl-btn"></label>
                </span>
                <Button loading={btnLoading["Save"]} variant="primary" size="medium" textAlign="center" onClick={() => handleSave("Save")}>
                  <Text as="span" variant="bodySm" fontWeight="medium">
                    Save
                  </Text>
                </Button>
              </InlineStack>
            </InlineGrid>
          </Box>
        </FullscreenBar>
        <div className="relative w-[100%]">
          <div className="flex flex-row justify-center mx-auto ">
            <div
              className="relative bg-white border scroll_custom overflow-y-auto hidden md:block"
              style={{ width: "100%", maxWidth: "320px", height: "calc(-58px + 100vh)" }}
            >
              <Box padding={"400"}>
                <BlockStack gap={"400"}>
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
                          showCharacterCount
                          maxLength={250}
                          autoComplete="off"
                        />
                      </div>
                    </BlockStack>
                  )}
                </BlockStack>
              </Box>
            </div>
            <div
              className="bg-white overflow-y-scroll email_customizer"
              style={{
                width: "100%",
                background: "rgb(246, 246, 247)",
                height: "calc(-58px + 100vh)",
              }}
            >
              <div className="flex items-stretch justify-center h-full">
                <div
                  className="w-full h-full overflow-hidden"
                  style={{
                    maxWidth: "100%",
                  }}
                >
                  <div className="h-full">
                    <div className="h-full shadow-border scroll_custom">
                      <div className="w-full h-full flex justify-center items-center">
                        <div class="image-container">
                          <div class="text-overlay">{customizedata?.message}</div>
                        </div>

                        {/* <div
                          className="slack_preview"
                          style={{ display: "flex", justifyContent: "center", alignItems: "end", paddingBottom: "134px", paddingLeft: "158px" }}
                        >
                          <div
                            style={{
                              height: "111px",
                              // background: "#000",
                              width: "264px",
                              borderRadius: "8px",
                              padding: "10px",
                              overflow: "hidden",
                              display: "flex",
                              alignItems: "flex-start",
                            }}
                          >
                            <p>{customizedata?.message}</p>
                          </div>
                        </div> */}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      {toastErrorMsg}
      {toastSuccessMsg}
    </>
  );
}
