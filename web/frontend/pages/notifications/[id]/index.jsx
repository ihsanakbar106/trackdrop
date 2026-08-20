import { useAppBridge, useNavigate } from "@shopify/app-bridge-react";
import {
  Avatar,
  Banner,
  BlockStack,
  Button,
  Card,
  Divider,
  FormLayout,
  Icon,
  InlineStack,
  Layout,
  LegacyCard,
  Link,
  List,
  Modal,
  Page,
  ResourceItem,
  ResourceList,
  SkeletonBodyText,
  SkeletonDisplayText,
  SkeletonPage,
  SkeletonTabs,
  SkeletonThumbnail,
  Spinner,
  Text,
  TextContainer,
  TextField,
  Thumbnail,
  Toast,
} from "@shopify/polaris";
import { ChevronRightIcon, ExternalSmallIcon } from "@shopify/polaris-icons";
import React, { useCallback, useContext, useEffect, useState } from "react";
import { AppContext } from "../../../components";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import { useLocation } from "react-router-dom";
import EmailNotification from "../../../components/EmailNotification";
import WebBaseNotification from "../../../components/WebBaseNotification";
import slackIcon from "../../../assets/slack_icon.png";

export default function NotificationDetails() {
  const navigate = useNavigate();
  const appBridge = useAppBridge();
  const location = useLocation();
  const notificationId = location?.pathname?.split("/").pop();
  const { apiUrl } = useContext(AppContext);
  const [loading, setLoading] = useState(true);
  const [btnLoading, setBtnLoading] = useState(false);
  const [errorToast, setErrorToast] = useState(false);
  const [successToast, setSuccessToast] = useState(false);
  const [toastMsg, setToastMsg] = useState("");
  const [toggleData, setToggleData] = useState(true);
  const [emailsList, setEmailsList] = useState([]);
  const [webNotificationList, setWebNotificationList] = useState([]);
  const [notificationType, setNotificationType] = useState("");
  const [slackSetting, setSlackSetting] = useState("");
  console.log("notificationType", notificationType);
  const [senderData, setSenderData] = useState({
    senderName: "",
    senderEmail: "",
    senderPhoneNumber: "",
  });
  const [disconnectedModal, setDisconnectedModal] = useState(false);

  const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
  const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);
  const handleChangeDisconnectedModal = useCallback(() => setDisconnectedModal(!disconnectedModal), [disconnectedModal]);
  const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

  const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}notification-detail/${notificationId}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { emails, web_notifications, sms_notifications, slack_notifications, notification_type, setting, slack_setting } = response?.data;
      setEmailsList(emails || web_notifications || sms_notifications || slack_notifications || []);
      setWebNotificationList(web_notifications || []);
      setNotificationType(notification_type || "");
      setSenderData((prevState) => ({
        ...prevState,
        senderName: setting?.sender_name,
        senderEmail: setting?.sender_email,
        senderPhoneNumber: setting?.from_sms_number,
      }));
      setSlackSetting(slack_setting || "");
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
      setToggleData(false);
      setBtnLoading(false);
    }
  };

  useEffect(() => {
    if (toggleData) {
      fetchData();
    }
  }, [toggleData]);

  const handleChangeValue = useCallback((field, value) => {
    setSenderData((prevState) => ({
      ...prevState,
      [field]: value,
    }));
  }, []);

  function convertNumberToBoolean(value) {
    let booleanValue;
    if (value === 1) {
      booleanValue = true;
    } else {
      booleanValue = false;
    }
    return booleanValue;
  }

  const handleCheckboxChangeIsActive = async (Id, value, notificationType) => {
    let sessionToken = await getSessionToken(appBridge);
    let enableValue = "";
    setBtnLoading((prev) => {
      let toggleId;
      if (prev[Id]) {
        toggleId = { [Id]: false };
      } else {
        toggleId = { [Id]: true };
      }
      return { ...toggleId };
    });

    if (value == 0) {
      enableValue = 1;
    } else {
      enableValue = 0;
    }
    try {
      const response = await axios.get(
        notificationType === "Email Notifications"
          ? `${apiUrl}email-status-save/${Id}?active_status=${enableValue}`
          : notificationType === "SMS Notifications"
          ? `${apiUrl}sms-notification-status-save/${Id}?active_status=${enableValue}`
          : notificationType === "Web-Based Push Notification"
          ? `${apiUrl}web-notification-status-save/${Id}?active_status=${enableValue}`
          : `${apiUrl}slack-notification-status-save/${Id}?active_status=${enableValue}`,
        {
          headers: {
            Authorization: `Bearer ${sessionToken}`,
          },
        },
      );
      if (response.data?.status == "success") {
        setSuccessToast(true);
        setToggleData(true);
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

  const handleNavigate = (id, notificationType) => {
    const target = event.target;
    const isCheckbox = target.tagName === "INPUT" || target.tagName === "LABEL";
    if (!isCheckbox) {
      navigate(
        notificationType === "Email Notifications"
          ? `/notifications/email-customize/${id}`
          : notificationType === "SMS Notifications"
          ? `/notifications/sms-notification/${id}`
          : notificationType === "Web-Based Push Notification"
          ? `/notifications/web-notification/${id}`
          : `/notifications/slack-notification/${id}`,
      );
      event.stopPropagation();
    }
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
        sender_name: senderData?.senderName,
        sender_email: senderData?.senderEmail,
      };

      const response = await axios.post(`${apiUrl}email-setting-save`, payload, {
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

  const handleSaveSMS = async (btnLoading) => {
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
        from_sms_number: senderData?.senderPhoneNumber,
      };

      const response = await axios.post(`${apiUrl}sms-setting-save`, payload, {
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

  const handleConnectSlack = async (btnLoading) => {
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
      const response = await axios.post(`${apiUrl}connect-slack`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      if (response?.data?.status === "success") {
        window.open(response?.data?.url);
      } else {
        setErrorToast(true);
        setToastMsg(response?.data.message);
      }
      setBtnLoading(false);
    } catch (error) {
      setBtnLoading(false);
    }
  };

  const handleDiscnnectSlack = async (btnLoading) => {
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
      const response = await axios.post(`${apiUrl}disconnect-slack-app`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      if (response?.data?.status === "success") {
        setToggleData(true);
        setSuccessToast(true);
        setToastMsg(response?.data?.message);
        setDisconnectedModal(false);
      } else {
        setErrorToast(true);
        setToastMsg(response?.data.message);
      }
    } catch (error) {
      setBtnLoading(false);
    }
  };

  return (
    <>
      <Modal
        open={disconnectedModal}
        onClose={handleChangeDisconnectedModal}
        title="Disconnect account"
        primaryAction={{
          content: "Disconnect",
          loading: btnLoading["Disconnect"],
          onAction: () => handleDiscnnectSlack("Disconnect"),
        }}
        secondaryActions={[
          {
            content: "Cancel",
            onAction: handleChangeDisconnectedModal,
          },
        ]}
      >
        <Modal.Section>
          <Text>Are you sure you want to disconnect your account?</Text>
        </Modal.Section>
      </Modal>

      {loading && notificationId !== "3" ? (
        <SkeletonPage primaryAction={notificationId === "1" ? true : false}>
          <Layout>
            <Layout.AnnotatedSection id="notifications" title={<SkeletonDisplayText size="small" />} description={<SkeletonBodyText />}>
              <Card>
                <Card>
                  <SkeletonBodyText lines={15} />
                </Card>
              </Card>
            </Layout.AnnotatedSection>
          </Layout>
        </SkeletonPage>
      ) : loading && notificationId == "3" ? (
        <SkeletonPage>
          <Layout>
            <Layout.Section>
              <Card>
                <Card>
                  <SkeletonBodyText lines={15} />
                </Card>
              </Card>
            </Layout.Section>
          </Layout>
        </SkeletonPage>
      ) : (
        <Page
          backAction={{ content: "Products", url: "/notifications" }}
          title={notificationType}
          primaryAction={
            notificationType === "Email Notifications"
              ? {
                  content: "Save",
                  loading: btnLoading["Save"],
                  onAction: () => handleSave("Save"),
                }
              : notificationType === "SMS Notifications"
              ? {
                  content: "Save",
                  loading: btnLoading["SMS"],
                  onAction: () => handleSaveSMS("SMS"),
                }
              : ""
          }
        >
          <Layout>
            {notificationType === "Email Notifications" && (
              <Layout.AnnotatedSection
                id="nativeShopifyNotification "
                title="Free"
                description={loading ? <SkeletonBodyText /> : "You can configure any add-ons listed in this section"}
              >
                <BlockStack gap={"400"}>
                  <Card>
                    <BlockStack gap={"400"}>
                      <BlockStack gap={"200"}>
                        <Text variant="headingSm" as="h6">
                          Shipping Events to Shopify
                        </Text>
                        {loading ? (
                          <SkeletonBodyText lines={5} />
                        ) : (
                          <Text variant="bodyMd" tone="subdued">
                            Send shipping updates to your Shopify store, enabling you to send native Shopify native notifications, view tracking
                            status on the order details page, and initiate review app notifications based on delivery events.
                          </Text>
                        )}
                      </BlockStack>
                      <BlockStack gap={"200"}>
                        <Text variant="headingSm" as="h6">
                          Enhanced Email Design with OrderlyEmails
                        </Text>
                        {loading ? (
                          <SkeletonBodyText lines={5} />
                        ) : (
                          <Text variant="bodyMd" tone="subdued">
                            Elevate your Shopify emails to a new level of professionalism using OrderlyEmails. Seamlessly integrate and ensure every
                            tracking link redirects to your custom-branded Rush tracking page effortlessly.
                          </Text>
                        )}
                      </BlockStack>
                      <BlockStack gap={"200"}>
                        <Text variant="headingSm" as="h6">
                          Enhanced Email Design with OrderlyEmails
                        </Text>
                        {loading ? (
                          <SkeletonBodyText lines={5} />
                        ) : (
                          <Text variant="bodyMd" tone="subdued">
                            Elevate your Shopify emails to a new level of professionalism using OrderlyEmails. Seamlessly integrate and ensure every
                            tracking link redirects to your custom-branded Rush tracking page effortlessly.
                          </Text>
                        )}
                      </BlockStack>
                    </BlockStack>
                  </Card>
                </BlockStack>
              </Layout.AnnotatedSection>
            )}

            {notificationType === "Email Notifications" && (
              <>
                <Layout.Section variant="fullWidth">
                  <Divider />
                </Layout.Section>
                <Layout.Section variant="oneThird">
                  <div style={{ marginTop: "var(--p-space-500)" }}>
                    <TextContainer>
                      <Text id="storeDetails" variant="headingMd" as="h2">
                        Sender from
                      </Text>
                      <Text tone="subdued" as="p">
                        Customers will see the set sender's email address and name when receiving the email
                      </Text>
                    </TextContainer>
                  </div>
                </Layout.Section>
                <Layout.Section>
                  <LegacyCard sectioned>
                    <BlockStack gap={"300"}>
                      <BlockStack gap={"100"}>
                        <Text as="h2" variant="headingSm">
                          Sender
                        </Text>
                        <Text as="span" variant="bodyMd" tone="subdued">
                          The sender of emails to customers.
                        </Text>
                      </BlockStack>
                      {loading ? (
                        <SkeletonDisplayText maxWidth="100%" />
                      ) : (
                        <TextField
                          onChange={(value) => handleChangeValue("senderName", value)}
                          value={senderData?.senderName}
                          label="Sender name"
                          labelHidden
                          autoComplete="off"
                        />
                      )}
                      <BlockStack gap={"100"}>
                        <Text as="h2" variant="headingSm">
                          Sender email
                        </Text>
                        <Text as="span" variant="bodyMd" tone="subdued">
                          The email your store uses to send emails to your customers.
                        </Text>
                      </BlockStack>
                      {loading ? (
                        <SkeletonDisplayText maxWidth="100%" />
                      ) : (
                        <TextField
                          onChange={(value) => handleChangeValue("senderEmail", value)}
                          value={senderData?.senderEmail}
                          label="Sender email"
                          labelHidden
                          autoComplete="off"
                        />
                      )}
                    </BlockStack>
                  </LegacyCard>
                </Layout.Section>
                <Layout.Section variant="oneThird">
                  <div style={{ marginTop: "var(--p-space-500)" }}>
                    <TextContainer>
                      <Text id="storeDetails" variant="headingMd" as="h2">
                        Paid
                      </Text>
                      <Text tone="subdued" as="p">
                        Email notifications are automatically sent to customers when conditions are met, custom rules will take priority.{" "}
                      </Text>
                    </TextContainer>
                  </div>
                </Layout.Section>
                <Layout.Section>
                  <Card>
                    <Card padding={0}>
                      <ResourceList
                        resourceName={{ singular: "customer", plural: "customers" }}
                        items={emailsList}
                        renderItem={(item, index) => {
                          const { id, title, active_status } = item;

                          return (
                            <ResourceItem
                              onClick={() => handleNavigate(id, notificationType)}
                              id={id}
                              accessibilityLabel={`View details for ${title}`}
                            >
                              <InlineStack align="space-between" blockAlign="center">
                                <BlockStack>
                                  <Text variant="headingSm" as="h6">
                                    {title}
                                  </Text>
                                  <Text variant="bodyMd" tone="subdued">
                                    Sent to you automatically after orders are in transit.
                                  </Text>
                                </BlockStack>
                                <InlineStack gap={"200"} blockAlign="center">
                                  {btnLoading[id] ? (
                                    <div
                                      className="toggleSpinner"
                                      style={{
                                        marginLeft: "0px",
                                        marginRight: "10px",
                                      }}
                                    >
                                      <Spinner size="small" />
                                    </div>
                                  ) : (
                                    <span>
                                      <input
                                        id={`toggle-${id}`}
                                        type="checkbox"
                                        name="status"
                                        className="tgl tgl-light"
                                        checked={convertNumberToBoolean(active_status)}
                                        onChange={() => {
                                          handleCheckboxChangeIsActive(id, active_status, notificationType);
                                        }}
                                      />
                                      <label htmlFor={`toggle-${id}`} className="tgl-btn"></label>
                                    </span>
                                  )}
                                  <Icon tone="base" source={ChevronRightIcon} />
                                </InlineStack>
                              </InlineStack>
                            </ResourceItem>
                          );
                        }}
                      />
                    </Card>
                  </Card>
                </Layout.Section>
              </>
            )}
            {notificationType === "SMS Notifications" && (
              <>
                <Layout.Section variant="oneThird">
                  <div style={{ marginTop: "var(--p-space-500)" }}>
                    <TextContainer>
                      <Text id="storeDetails" variant="headingMd" as="h2">
                        Sender from
                      </Text>
                      <Text tone="subdued" as="p">
                        Customers will see the set sender's email address and name when receiving the email
                      </Text>
                    </TextContainer>
                  </div>
                </Layout.Section>
                <Layout.Section>
                  <LegacyCard sectioned>
                    <BlockStack gap={"300"}>
                      <BlockStack>
                        <Text as="h2" variant="headingSm">
                          Sender phone number
                        </Text>
                        <Text as="span" variant="bodyMd" tone="subdued">
                          The sender phone number to customers.
                        </Text>
                      </BlockStack>
                      {loading ? (
                        <SkeletonDisplayText maxWidth="100%" />
                      ) : (
                        <TextField
                          onChange={(value) => handleChangeValue("senderPhoneNumber", value)}
                          value={senderData?.senderPhoneNumber}
                          label="Sender phone number"
                          labelHidden
                          autoComplete="off"
                        />
                      )}
                    </BlockStack>
                  </LegacyCard>
                </Layout.Section>
                <Layout.Section variant="oneThird">
                  <div style={{ marginTop: "var(--p-space-500)" }}>
                    <TextContainer>
                      <Text id="storeDetails" variant="headingMd" as="h2">
                        Paid
                      </Text>
                      <Text tone="subdued" as="p">
                        Email notifications are automatically sent to customers when conditions are met, custom rules will take priority.{" "}
                      </Text>
                    </TextContainer>
                  </div>
                </Layout.Section>
                <Layout.Section>
                  <Card>
                    <Card padding={0}>
                      <ResourceList
                        resourceName={{ singular: "customer", plural: "customers" }}
                        items={emailsList}
                        renderItem={(item, index) => {
                          const { id, title, active_status } = item;

                          return (
                            <ResourceItem
                              onClick={() => handleNavigate(id, notificationType)}
                              id={id}
                              accessibilityLabel={`View details for ${title}`}
                            >
                              <InlineStack align="space-between" blockAlign="center">
                                <BlockStack>
                                  <Text variant="headingSm" as="h6">
                                    {title}
                                  </Text>
                                  <Text variant="bodyMd" tone="subdued">
                                    Sent to you automatically after orders are in transit.
                                  </Text>
                                </BlockStack>
                                <InlineStack gap={"200"} blockAlign="center">
                                  {btnLoading[id] ? (
                                    <div
                                      className="toggleSpinner"
                                      style={{
                                        marginLeft: "0px",
                                        marginRight: "10px",
                                      }}
                                    >
                                      <Spinner size="small" />
                                    </div>
                                  ) : (
                                    <span>
                                      <input
                                        id={`toggle-${id}`}
                                        type="checkbox"
                                        name="status"
                                        className="tgl tgl-light"
                                        checked={convertNumberToBoolean(active_status)}
                                        onChange={() => {
                                          handleCheckboxChangeIsActive(id, active_status, notificationType);
                                        }}
                                      />
                                      <label htmlFor={`toggle-${id}`} className="tgl-btn"></label>
                                    </span>
                                  )}
                                  <Icon tone="base" source={ChevronRightIcon} />
                                </InlineStack>
                              </InlineStack>
                            </ResourceItem>
                          );
                        }}
                      />
                    </Card>
                  </Card>
                </Layout.Section>
              </>
            )}
            {notificationType === "Web-Based Push Notification" && (
              <Layout.Section>
                <Card>
                  <Card padding={0}>
                    <ResourceList
                      resourceName={{ singular: "customer", plural: "customers" }}
                      items={emailsList}
                      renderItem={(item, index) => {
                        const { id, title, active_status } = item;

                        return (
                          <ResourceItem onClick={() => handleNavigate(id, notificationType)} id={id} accessibilityLabel={`View details for ${title}`}>
                            <InlineStack align="space-between" blockAlign="center">
                              <BlockStack>
                                <Text variant="headingSm" as="h6">
                                  {title}
                                </Text>
                                <Text variant="bodyMd" tone="subdued">
                                  Sent to you automatically after orders are in transit.
                                </Text>
                              </BlockStack>
                              <InlineStack gap={"200"} blockAlign="center">
                                {btnLoading[id] ? (
                                  <div
                                    className="toggleSpinner"
                                    style={{
                                      marginLeft: "0px",
                                      marginRight: "10px",
                                    }}
                                  >
                                    <Spinner size="small" />
                                  </div>
                                ) : (
                                  <span>
                                    <input
                                      id={`toggle-${id}`}
                                      type="checkbox"
                                      name="status"
                                      className="tgl tgl-light"
                                      checked={convertNumberToBoolean(active_status)}
                                      onChange={() => {
                                        handleCheckboxChangeIsActive(id, active_status, notificationType);
                                      }}
                                    />
                                    <label htmlFor={`toggle-${id}`} className="tgl-btn"></label>
                                  </span>
                                )}
                                <Icon tone="base" source={ChevronRightIcon} />
                              </InlineStack>
                            </InlineStack>
                          </ResourceItem>
                        );
                      }}
                    />
                  </Card>
                </Card>
              </Layout.Section>
            )}
            {notificationType === "Slack Notification" && slackSetting !== "" && (
              <>
                <Layout.Section variant="fullWidth">
                  <Card>
                    <Banner
                      tone="info"
                      action={{
                        content: "Disconnect account",
                        onAction: handleChangeDisconnectedModal,
                      }}
                    >
                      <p>Your Slack is now connected! You will receive notifications through Slack.</p>
                    </Banner>
                  </Card>
                </Layout.Section>
                <Layout.AnnotatedSection
                  id="notifications"
                  title="Paid"
                  description="Email notifications are automatically sent to customers when conditions are met, custom rules will take priority."
                >
                  <Card>
                    <Card padding={0}>
                      <ResourceList
                        resourceName={{ singular: "customer", plural: "customers" }}
                        items={emailsList}
                        renderItem={(item, index) => {
                          const { id, title, active_status } = item;

                          return (
                            <ResourceItem
                              onClick={() => handleNavigate(id, notificationType)}
                              id={id}
                              accessibilityLabel={`View details for ${title}`}
                            >
                              <InlineStack align="space-between" blockAlign="center">
                                <BlockStack>
                                  <Text variant="headingSm" as="h6">
                                    {title}
                                  </Text>
                                  <Text variant="bodyMd" tone="subdued">
                                    Sent to you automatically after orders are in transit.
                                  </Text>
                                </BlockStack>
                                <InlineStack gap={"200"} blockAlign="center">
                                  {btnLoading[id] ? (
                                    <div
                                      className="toggleSpinner"
                                      style={{
                                        marginLeft: "0px",
                                        marginRight: "10px",
                                      }}
                                    >
                                      <Spinner size="small" />
                                    </div>
                                  ) : (
                                    <span>
                                      <input
                                        id={`toggle-${id}`}
                                        type="checkbox"
                                        name="status"
                                        className="tgl tgl-light"
                                        checked={convertNumberToBoolean(active_status)}
                                        onChange={() => {
                                          handleCheckboxChangeIsActive(id, active_status, notificationType);
                                        }}
                                      />
                                      <label htmlFor={`toggle-${id}`} className="tgl-btn"></label>
                                    </span>
                                  )}
                                  <Icon tone="base" source={ChevronRightIcon} />
                                </InlineStack>
                              </InlineStack>
                            </ResourceItem>
                          );
                        }}
                      />
                    </Card>
                  </Card>
                </Layout.AnnotatedSection>
              </>
            )}
            {notificationType === "Slack Notification" && slackSetting == "" && (
              <Layout.Section>
                <Card>
                  <Banner
                    tone="warning"
                    action={{ content: "Connect account", loading: btnLoading["ConnectSlack"], onAction: () => handleConnectSlack("ConnectSlack") }}
                  >
                    <p>Connect your slack account to enable slack notifications.</p>
                  </Banner>
                </Card>
              </Layout.Section>
            )}
            <Layout.Section></Layout.Section>
            <Layout.Section></Layout.Section>
          </Layout>
          {toastSuccessMsg}
          {toastErrorMsg}
        </Page>
      )}
    </>
  );
}
