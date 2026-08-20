import { useAppBridge, useNavigate } from "@shopify/app-bridge-react";
import {
  Avatar,
  BlockStack,
  Button,
  Card,
  FormLayout,
  Grid,
  Icon,
  InlineStack,
  Layout,
  LegacyCard,
  LegacyStack,
  Link,
  Page,
  ResourceItem,
  ResourceList,
  SkeletonBodyText,
  SkeletonDisplayText,
  SkeletonPage,
  Text,
  TextContainer,
  TextField,
  Toast,
  Tooltip,
} from "@shopify/polaris";
import { ChevronRightIcon } from "@shopify/polaris-icons";
import React, { useCallback, useContext, useEffect, useState } from "react";
import { AppContext } from "../../components";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";

export default function Notification() {
  const navigate = useNavigate();
  const appBridge = useAppBridge();
  const { apiUrl } = useContext(AppContext);
  const [loading, setLoading] = useState(true);
  const [btnLoading, setBtnLoading] = useState(false);
  const [errorToast, setErrorToast] = useState(false);
  const [successToast, setSuccessToast] = useState(false);
  const [toastMsg, setToastMsg] = useState("");
  const [toggleData, setToggleData] = useState(true);
  const [notificationList, setNotificationList] = useState([]);

  const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
  const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

  const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

  const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}notifications`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { notifications } = response?.data;
      setNotificationList(notifications || []);
    } catch (error) {
    } finally {
      setLoading(false);
      setToggleData(false);
    }
  };

  useEffect(() => {
    if (toggleData) {
      fetchData();
    }
  }, [toggleData]);

  function convertNumberToBoolean(value) {
    let booleanValue;
    if (value === 1) {
      booleanValue = true;
    } else {
      booleanValue = false;
    }
    return booleanValue;
  }

  const handleCheckboxChangeIsActive = (id, currentStatus) => {
    const updatedNotifications = notificationList.map((notification) => {
      if (notification.id === id) {
        return { ...notification, active_status: currentStatus == 0 ? 1 : 0 };
      }
      return notification;
    });

    setNotificationList(updatedNotifications);
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
        notification_ids: notificationList?.map((notification) => notification?.id).join(","),
        notification_statuses: notificationList?.map((notification) => notification?.active_status).join(","),
      };

      const response = await axios.post(`${apiUrl}notification-save`, payload, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });

      console.log("responseresponse", response);
      setBtnLoading(false);
      setSuccessToast(true);
      setToastMsg(response?.data?.message);
    } catch (error) {
      setBtnLoading(false);
    }
  };

  const handleNavigate = (id) => {
    const target = event.target;
    const isCheckbox = target.tagName === "INPUT" || target.tagName === "LABEL";
    if (!isCheckbox) {
      navigate(`/notifications/${id}`);
      event.stopPropagation();
    }
  };

  return loading ? (
    <SkeletonPage primaryAction>
      <Layout>
        <Layout.Section>
          <Grid gap={{ xs: "1rem", sm: "1rem", md: "1rem", lg: "1rem", xl: "1rem" }}>
            <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 6, lg: 6, xl: 4 }}>
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack gap={"300"}>
                    <SkeletonDisplayText />
                    <SkeletonBodyText lines={3} />
                  </BlockStack>
                </BlockStack>
              </Card>
            </Grid.Cell>
            <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 6, lg: 6, xl: 4 }}>
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack gap={"300"}>
                    <SkeletonDisplayText />
                    <SkeletonBodyText lines={3} />
                  </BlockStack>
                </BlockStack>
              </Card>
            </Grid.Cell>
            <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 6, lg: 6, xl: 4 }}>
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack gap={"300"}>
                    <SkeletonDisplayText />
                    <SkeletonBodyText lines={3} />
                  </BlockStack>
                </BlockStack>
              </Card>
            </Grid.Cell>
            <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 6, lg: 6, xl: 4 }}>
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack gap={"300"}>
                    <SkeletonDisplayText />
                    <SkeletonBodyText lines={3} />
                  </BlockStack>
                </BlockStack>
              </Card>
            </Grid.Cell>
          </Grid>
        </Layout.Section>
      </Layout>
    </SkeletonPage>
  ) : (
    <Page
      title="Notifications"
      primaryAction={{
        content: "Save",
        loading: btnLoading["Save"],
        onAction: () => handleSave("Save"),
      }}
    >
      <Layout>
        <Layout.Section>
          <Grid gap={{ xs: "1rem", sm: "1rem", md: "1rem", lg: "1rem", xl: "1rem" }}>
            {notificationList?.map((item, index) => {
              return (
                <Grid.Cell key={index} columnSpan={{ xs: 6, sm: 6, md: 6, lg: 6, xl: 4 }}>
                  <Card>
                    <BlockStack gap={"400"}>
                      <BlockStack gap={"200"}>
                        <InlineStack align="start" blockAlign="center">
                          <Button variant="monochromePlain" size="medium" textAlign="center" onClick={() => handleNavigate(item?.id)}>
                            <Text variant="headingMd" as="h2">
                              {item?.title}
                            </Text>
                          </Button>
                        </InlineStack>
                        <Text variant="bodyMd" tone="subdued">
                          Sent to you automatically after orders are in transit.
                        </Text>
                        <InlineStack align="end" gap={"200"} blockAlign="center">
                          <span>
                            <input
                              id={`toggle-${item?.id}`}
                              type="checkbox"
                              name="status"
                              className="tgl tgl-light"
                              checked={convertNumberToBoolean(item?.active_status)}
                              onChange={() => handleCheckboxChangeIsActive(item?.id, item?.active_status)}
                            />
                            <label htmlFor={`toggle-${item?.id}`} className="tgl-btn"></label>
                          </span>
                        </InlineStack>
                      </BlockStack>
                    </BlockStack>
                  </Card>
                </Grid.Cell>
              );
            })}
          </Grid>
        </Layout.Section>
        {/* <Layout.AnnotatedSection
          id="storeDetails"
          title="Sender from"
          description={loading ? <SkeletonBodyText /> : "Customers will see the set sender's email address and name when receiving the email"}
        >
          <Card>
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
          </Card>
        </Layout.AnnotatedSection>
        <Layout.AnnotatedSection
          id="notifications"
          title="Notifications"
          description={
            loading ? (
              <SkeletonBodyText />
            ) : (
              "Email notifications are automatically sent to customers when conditions are met, custom rules will take priority."
            )
          }
        >
          <Card>
            {loading ? (
              <Card>
                <SkeletonBodyText lines={10} />
              </Card>
            ) : (
              <Card padding={0}>
                <ResourceList
                  resourceName={{ singular: "customer", plural: "customers" }}
                  items={notificationList}
                  renderItem={(item, index) => {
                    const { id, title, active_status } = item;

                    return (
                      <ResourceItem onClick={() => handleNavigate(id)} id={id} accessibilityLabel={`View details for ${title}`}>
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
                            <span>
                              <input
                                id={`toggle-${id}`}
                                type="checkbox"
                                name="status"
                                className="tgl tgl-light"
                                checked={convertNumberToBoolean(active_status)}
                                onChange={() => handleCheckboxChangeIsActive(id, active_status)}
                              />
                              <label htmlFor={`toggle-${id}`} className="tgl-btn"></label>
                            </span>
                            <Icon tone="base" source={ChevronRightIcon} />
                          </InlineStack>
                        </InlineStack>
                      </ResourceItem>
                    );
                  }}
                />
              </Card>
            )}
          </Card>
        </Layout.AnnotatedSection> */}
      </Layout>
      {toastSuccessMsg}
      {toastErrorMsg}
    </Page>
  );
}
