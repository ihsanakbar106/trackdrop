import { useAppBridge, useNavigate } from "@shopify/app-bridge-react";
import {
  Page,
  Badge,
  LegacyCard,
  InlineStack,
  Layout,
  Card,
  Text,
  BlockStack,
  Grid,
  InlineGrid,
  Thumbnail,
  Button,
  Tooltip,
  Icon,
  Box,
  ProgressBar,
  Link,
  Toast,
  Divider,
  SkeletonDisplayText,
  SkeletonBodyText,
  SkeletonThumbnail,
  SkeletonPage,
} from "@shopify/polaris";
import React, { useCallback, useContext, useEffect, useState } from "react";
import { useLocation } from "react-router-dom";
import { AppContext } from "../../components";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import { ClipboardIcon, StatusActiveIcon } from "@shopify/polaris-icons";

function formatDate(input) {
  if (!input) return "";
  const date = new Date(input);
  if (Number.isNaN(date.getTime())) return "";

  const months = ["Jan.", "Feb.", "Mar.", "Apr.", "May", "Jun.", "Jul.", "Aug.", "Sep.", "Oct.", "Nov.", "Dec."];
  const year = date.getUTCFullYear();
  const month = months[date.getUTCMonth()];
  const day = ("0" + date.getUTCDate()).slice(-2);
  let hours = date.getUTCHours();
  const minutes = ("0" + date.getUTCMinutes()).slice(-2);
  const period = hours >= 12 ? "pm" : "am";
  hours = hours % 12 || 12;
  hours = ("0" + hours).slice(-2);

  return `${month} ${day}, ${year} ${hours}:${minutes} ${period}`;
}

function capitalizeWords(str) {
  return str?.replace(/\b\w/g, (char) => char.toUpperCase());
}

/** Supports ISO (…T…) and Cargo-style "Y-m-d H:i:s" / missing dates. */
const formatDateTracking = (dateString) => {
  if (dateString == null || dateString === "") return "";

  const raw = String(dateString).trim();
  const months = ["Jan.", "Feb.", "Mar.", "Apr.", "May.", "Jun.", "Jul.", "Aug.", "Sep.", "Oct.", "Nov.", "Dec."];

  // Normalize "2026-09-07 12:56:00" → parseable; strip timezone suffix after time if present
  const normalized = raw.includes("T") ? raw : raw.replace(" ", "T");
  const date = new Date(normalized);
  if (Number.isNaN(date.getTime())) return raw;

  const month = months[date.getMonth()];
  const day = date.getDate();
  const year = date.getFullYear();

  let hour = date.getHours();
  const minutes = date.getMinutes().toString().padStart(2, "0");
  const ampm = hour >= 12 ? "PM" : "AM";
  const hours24 = hour.toString().padStart(2, "0");

  return `${month} ${day}, ${year} ${hours24}:${minutes} ${ampm}`;
};

export default function DetailsShipment() {
  const navigate = useNavigate();
  const appBridge = useAppBridge();
  const location = useLocation();
  const id = location?.pathname?.split("/").pop();
  const { apiUrl, shop } = useContext(AppContext);
  const [toggleLoadData, setToggleLoadData] = useState(true);
  const [btnLoading, setBtnLoading] = useState(false);
  const [loading, setLoading] = useState(true);
  const [errorToast, setErrorToast] = useState(false);
  const [successToast, setSuccessToast] = useState(false);
  const [toastMsg, setToastMsg] = useState("");
  const [fulfillmentData, setFulfillmentData] = useState("");
  const [customerDetails, setCustomerDetails] = useState("");
  const [shippingAddress, setShippingAddress] = useState("");
  const [lineItems, setLineItems] = useState([]);
  const [carrierDetail, setCarrierDetail] = useState("");
  const [trackingInfo, setTrackingInfo] = useState([]);
  const [showMore, setShowMore] = useState(false);

  const [transitDays, setTransitDays] = useState(null);

  // -------------------------- TOAST MESSAGE --------------------------
  const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
  const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

  const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

  const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}fulfillment_detail/${id}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { fulfillment_data, carrier_detail } = response?.data;
      setFulfillmentData(fulfillment_data);

      const parseJson = (value, fallback) => {
        if (value == null || value === "") return fallback;
        if (typeof value === "object") return value;
        try {
          return JSON.parse(value);
        } catch {
          return fallback;
        }
      };

      setCustomerDetails(parseJson(fulfillment_data?.order?.customer, ""));
      const trackInfo = parseJson(fulfillment_data?.track_info, []);
      setTrackingInfo(Array.isArray(trackInfo) ? trackInfo : []);
      setShippingAddress(parseJson(fulfillment_data?.order?.shipping_address, ""));
      setLineItems(fulfillment_data?.order?.lineitems || []);
      setCarrierDetail(carrier_detail);
      // Create Date objects from the input strings
      const startDate = new Date(fulfillment_data?.first_date);
      const endDate = new Date(fulfillment_data?.last_date);

      // Calculate the difference in milliseconds
      const diffTime = Math.abs(endDate - startDate);

      // Convert milliseconds to days
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

      setTransitDays(diffDays);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setBtnLoading(false);
      setLoading(false);
      setToggleLoadData(false);
    }
  };

  useEffect(() => {
    if (toggleLoadData) {
      fetchData();
    }
  }, [toggleLoadData]);

  const copyToClipboard = async (text) => {
    try {
      await navigator.clipboard.writeText(text);
      setSuccessToast(true);
      setToastMsg("Copied!");
    } catch (err) {
      setErrorToast(true);
      setToastMsg("Failed to copy!");
    }
  };

  const handleButtonShowMore = () => {
    setShowMore(true);
  };

  const handleAddPage = async (btnLoading) => {
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

      const response = await axios.get(`${apiUrl}update-carrier/${fulfillmentData?.order?.shopify_order_id}`, {
        headers: {
          "Content-Type": "multipart/form-data",
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      setToggleLoadData(true);
      setToastMsg(response?.data?.message || "Tracking refreshed.");
      if (response?.data?.status === "success") {
        setSuccessToast(true);
      } else {
        setErrorToast(true);
      }
    } catch (error) {
      setBtnLoading(false);
      setErrorToast(true);
      setToastMsg(error?.response?.data?.message || "Failed to refresh tracking.");
    }
  };

  return loading ? (
    <SkeletonPage primaryAction>
      <Layout>
        <Layout.Section>
          <BlockStack gap={"400"}>
            <Card>
              <BlockStack gap={"300"}>
                <SkeletonDisplayText />
                <Box>
                  <SkeletonBodyText lines={1} />
                </Box>
                <Box>
                  <BlockStack gap={"500"}>
                    <SkeletonBodyText lines={2} />
                    <SkeletonBodyText lines={2} />
                    <SkeletonBodyText lines={2} />
                    <SkeletonBodyText lines={2} />
                    <SkeletonBodyText lines={2} />
                    <SkeletonBodyText lines={2} />
                    <SkeletonBodyText lines={2} />
                  </BlockStack>
                </Box>
              </BlockStack>
            </Card>
            <Card>
              <BlockStack gap={"300"}>
                <SkeletonDisplayText />
                <Box>
                  <SkeletonBodyText lines={1} />
                </Box>
                <Box>
                  <BlockStack gap={"500"}>
                    <SkeletonBodyText lines={2} />
                    <SkeletonBodyText lines={2} />
                    <SkeletonBodyText lines={2} />
                  </BlockStack>
                </Box>
              </BlockStack>
            </Card>
          </BlockStack>
        </Layout.Section>
        <Layout.Section variant="oneThird">
          <BlockStack gap={"400"}>
            <Card>
              <BlockStack gap={"300"}>
                <SkeletonDisplayText size="small" />
                <InlineStack blockAlign="center" wrap={false}>
                  <div
                    className="items-center w-full gap-2"
                    style={{
                      display: "grid",
                      gridTemplateColumns: "auto 1fr",
                    }}
                  >
                    <SkeletonThumbnail size="small" />
                    <InlineStack align="space-between" blockAlign="center">
                      <SkeletonBodyText />
                    </InlineStack>
                  </div>
                </InlineStack>
                <SkeletonDisplayText size="small" />
                <SkeletonBodyText lines={2} />
              </BlockStack>
            </Card>
            <Card>
              <BlockStack gap={"300"}>
                <SkeletonDisplayText size="small" />
                <SkeletonBodyText />
              </BlockStack>
            </Card>
            <Card>
              <BlockStack gap={"300"}>
                <SkeletonDisplayText size="small" />
                <SkeletonBodyText lines={1} />
                <SkeletonDisplayText size="small" />
                <SkeletonBodyText lines={1} />
                <SkeletonDisplayText size="small" />
                <SkeletonBodyText lines={6} />
              </BlockStack>
            </Card>
          </BlockStack>
        </Layout.Section>
        <Layout.Section></Layout.Section>
      </Layout>
    </SkeletonPage>
  ) : (
    <Page
      backAction={{
        content: "Products",
        onAction: () => navigate("/orders"),
      }}
      title={fulfillmentData?.order?.name}
      titleMetadata={
        <InlineStack gap={"100"}>
          <Badge tone={capitalizeWords(fulfillmentData?.shipment_status) === "Delivered" ? "success" : "warning"} progress="complete">
            {capitalizeWords(fulfillmentData?.shipment_status) === "Notfound" || capitalizeWords(fulfillmentData?.shipment_status) == null
              ? "Pending"
              : capitalizeWords(fulfillmentData?.shipment_status)}
          </Badge>
        </InlineStack>
      }
      subtitle={formatDate(fulfillmentData?.order?.created_at)}
      compactTitle
      secondaryActions={[
        {
          content: "Refresh",
          loading: btnLoading["Refresh"],
          onAction: () => handleAddPage("Refresh"),
        },
      ]}
    >
      <Layout>
        <Layout.Section>
          <div className="shipmentTrackingInfo">
            <Card>
              <BlockStack gap={"300"}>
                <Text as="h2" variant="headingLg">
                  {capitalizeWords(fulfillmentData?.shipment_status) === "Notfound" || capitalizeWords(fulfillmentData?.shipment_status) == null
                    ? "Pending"
                    : capitalizeWords(fulfillmentData?.shipment_status)}
                </Text>
                <Box>
                  <ProgressBar
                    tone={capitalizeWords(fulfillmentData?.shipment_status) === "Notfound" ? "primary" : "success"}
                    size="small"
                    progress={capitalizeWords(fulfillmentData?.shipment_status) === "Notfound" ? 1 : 100}
                  />
                </Box>
                <Box>
                  <ul className="m-0 p-2 w-full list-none">
                    {trackingInfo?.length ? (
                      <>
                        {trackingInfo?.slice(0, 2)?.map((item, index) => (
                          <li key={index} className="relative m-0 p-0 pb-6 list-none semi-timeline-item">
                            <div className="semi-timeline-item-tail" aria-hidden="true"></div>
                            <div
                              className="semi-timeline-item-head semi-timeline-item-head-custom semi-timeline-item-head-default"
                              aria-hidden="true"
                            >
                              {index == 0 ? (
                                <span className="Polaris-Icon">
                                  <svg viewBox="0 0 20 20" className="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                                    <path
                                      fill="rgb(41, 132, 90)"
                                      d="M13.28 9.22a.75.75 0 0 1 0 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-1.75-1.75a.75.75 0 1 1 1.06-1.06l1.22 1.22 2.97-2.97a.75.75 0 0 1 1.06 0Z"
                                    ></path>
                                    <path
                                      fill="rgb(41, 132, 90)"
                                      fill-rule="evenodd"
                                      d="M6.515 4.75a2 2 0 0 1 1.985-1.75h3a2 2 0 0 1 1.985 1.75h.265a2.25 2.25 0 0 1 2.25 2.25v7.75a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-7.75a2.25 2.25 0 0 1 2.25-2.25h.265Zm1.985-.25h3a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5Zm-1.987 1.73.002.02h-.265a.75.75 0 0 0-.75.75v7.75c0 .414.336.75.75.75h7.5a.75.75 0 0 0 .75-.75v-7.75a.75.75 0 0 0-.75-.75h-.265a2 2 0 0 1-1.985 1.75h-3a2 2 0 0 1-1.987-1.77Z"
                                    ></path>
                                  </svg>
                                </span>
                              ) : (
                                <span className="Polaris-Icon">
                                  <svg viewBox="0 0 20 20" className="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                                    <path
                                      fill="rgb(255, 230, 0)"
                                      fill-rule="evenodd"
                                      d="M15 4.25a.75.75 0 0 0-1.5 0v3.98l-.8-2.001a2.75 2.75 0 0 0-2.554-1.729h-3.396a.75.75 0 0 0-.75.75v3.25h-1.75a.75.75 0 0 0-.75.75v3.159a2.5 2.5 0 1 0 4.636 1.841h2.479a2.501 2.501 0 0 0 4.835-1.25h1.8a.75.75 0 0 0 0-1.5h-2.25v-7.25Zm-8.589 10a.998.998 0 0 0 0-1.5.996.996 0 0 0-1.322 0 .998.998 0 0 0 0 1.5.996.996 0 0 0 1.322 0Zm-1.411-3.136a2.501 2.501 0 0 1 3.136 1.636h2.479a2.502 2.502 0 0 1 2.135-1.738v-.012h-3.25a.75.75 0 0 1-.256-.045l-2.626-.955h-1.618v1.114Zm7.338 1.636a1 1 0 1 1 1.323 1.498 1 1 0 0 1-1.322-1.498Zm-2.706-3.25h2.76l-1.085-2.714a1.25 1.25 0 0 0-1.161-.786h-2.646v2.725l2.132.775Z"
                                    ></path>
                                  </svg>
                                </span>
                              )}
                            </div>
                            <div className="semi-timeline-item-content">
                              <p className="Polaris-Text--root Polaris-Text--bodyMd">{item?.location}</p>
                              <div className="semi-timeline-item-content-time">
                                <p className="Polaris-Text--root Polaris-Text--bodyMd Polaris-Text--subdued">
                                  {formatDateTracking(item?.checkpoint_date)} · {item?.tracking_detail}
                                </p>
                              </div>
                            </div>
                          </li>
                        ))}
                        {!showMore && (
                          <li className="relative m-0 p-0 pb-6 list-none semi-timeline-item">
                            <div className="semi-timeline-item-tail" aria-hidden="true"></div>
                            <div
                              className="semi-timeline-item-head semi-timeline-item-head-custom semi-timeline-item-head-default"
                              aria-hidden="true"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="12" viewBox="0 0 13 12" fill="none">
                                <circle cx="6.5" cy="6" r="6" fill="white"></circle>
                                <circle cx="6.5" cy="6" r="5.5" stroke="#DDDDDD" stroke-opacity="0.75"></circle>
                              </svg>
                            </div>
                            <div className="semi-timeline-item-content">
                              <Button onClick={handleButtonShowMore} variant="plain" size="medium" textAlign="center">
                                <Text as="span" variant="bodyMd" fontWeight="regular">
                                  Show more updates&nbsp;(
                                  {trackingInfo?.slice(2, -2)?.length})
                                </Text>
                              </Button>
                            </div>
                          </li>
                        )}
                        {showMore &&
                          trackingInfo?.slice(2, -2)?.map((item, index) => (
                            <li key={index} className="relative m-0 p-0 pb-6 list-none semi-timeline-item">
                              {" "}
                              <div className="semi-timeline-item-tail" aria-hidden="true"></div>
                              <div
                                className="semi-timeline-item-head semi-timeline-item-head-custom semi-timeline-item-head-default"
                                aria-hidden="true"
                              >
                                <Icon source={StatusActiveIcon} tone="success" />
                                {/* <svg xmlns="http://www.w3.org/2000/svg" width="13" height="12" viewBox="0 0 13 12" fill="none">
                                  <circle cx="6.5" cy="6" r="6" fill="white"></circle>
                                  <circle cx="6.5" cy="6" r="5.5" stroke="#DDDDDD" stroke-opacity="0.75"></circle>
                                </svg> */}
                              </div>
                              <div className="semi-timeline-item-content">
                                <p className="Polaris-Text--root Polaris-Text--bodyMd">{item?.location}</p>
                                <div className="semi-timeline-item-content-time">
                                  <p className="Polaris-Text--root Polaris-Text--bodyMd Polaris-Text--subdued">
                                    {formatDateTracking(item?.checkpoint_date)} · {item?.tracking_detail}
                                  </p>
                                </div>
                              </div>
                            </li>
                          ))}
                        {trackingInfo?.slice(-2)?.map((item, index) => (
                          <li key={index} className="relative m-0 p-0 pb-6 list-none semi-timeline-item">
                            <div className="semi-timeline-item-tail" aria-hidden="true"></div>
                            <div
                              className="semi-timeline-item-head semi-timeline-item-head-custom semi-timeline-item-head-default"
                              aria-hidden="true"
                            >
                              {index == 0 ? (
                                <span className="Polaris-Icon">
                                  <svg viewBox="0 0 20 20" className="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                                    <path
                                      fill="rgb(0, 91, 211)"
                                      fill-rule="evenodd"
                                      d="M4 5.25a.75.75 0 0 1 .75-.75h6.991a2.75 2.75 0 0 1 2.645 1.995l.427 1.494a.25.25 0 0 0 .18.173l1.681.421a1.75 1.75 0 0 1 1.326 1.698v1.219a1.75 1.75 0 0 1-1.032 1.597 2.5 2.5 0 1 1-4.955.153h-3.025a2.5 2.5 0 1 1-4.78-.75h-.458a.75.75 0 0 1 0-1.5h2.5c.03 0 .06.002.088.005a2.493 2.493 0 0 1 1.947.745h4.43a2.493 2.493 0 0 1 1.785-.75c.698 0 1.33.286 1.783.748a.25.25 0 0 0 .217-.248v-1.22a.25.25 0 0 0-.19-.242l-1.682-.42a1.75 1.75 0 0 1-1.258-1.217l-.427-1.494a1.25 1.25 0 0 0-1.202-.907h-6.991a.75.75 0 0 1-.75-.75Zm2.5 9.25a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                                    ></path>
                                    <path d="M3.25 8a.75.75 0 0 0 0 1.5h5a.75.75 0 0 0 0-1.5h-5Z"></path>
                                  </svg>
                                </span>
                              ) : (
                                <span className="Polaris-Icon">
                                  <svg viewBox="0 0 20 20" className="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                                    <path fill="rgb(0, 148, 213)" d="M6.55 7.25a.7.7 0 0 1 .7-.7h5.5a.7.7 0 0 1 0 1.4h-5.5a.7.7 0 0 1-.7-.7Z"></path>
                                    <path fill="rgb(0, 148, 213)" d="M7 9.05a.7.7 0 0 0 0 1.4h2.25a.7.7 0 1 0 0-1.4h-2.25Z"></path>
                                    <path
                                      fill="rgb(0, 148, 213)"
                                      fill-rule="evenodd"
                                      d="M3.5 6.25a2.75 2.75 0 0 1 2.75-2.75h7.5a2.75 2.75 0 0 1 2.75 2.75v5.5a.75.75 0 0 1-.22.53l-4 4a.75.75 0 0 1-.53.22h-5.5a2.75 2.75 0 0 1-2.75-2.75v-7.5Zm2.75-1.25c-.69 0-1.25.56-1.25 1.25v7.5c0 .69.56 1.25 1.25 1.25h4.75v-2.25c0-.966.784-1.75 1.75-1.75h2.25v-4.75c0-.69-.56-1.25-1.25-1.25h-7.5Zm7.69 7.5h-1.19a.25.25 0 0 0-.25.25v1.19l1.44-1.44Z"
                                    ></path>
                                  </svg>
                                </span>
                              )}
                            </div>
                            <div className="semi-timeline-item-content">
                              <p className="Polaris-Text--root Polaris-Text--bodyMd">{item?.location}</p>
                              <div className="semi-timeline-item-content-time">
                                <p className="Polaris-Text--root Polaris-Text--bodyMd Polaris-Text--subdued">
                                  {formatDateTracking(item?.checkpoint_date)} · {item?.tracking_detail}
                                </p>
                              </div>
                            </div>
                          </li>
                        ))}
                      </>
                    ) : (
                      <li className="relative m-0 p-0 pb-6 list-none semi-timeline-item">
                        <div className="semi-timeline-item-tail" aria-hidden="true"></div>
                        <div className="semi-timeline-item-head semi-timeline-item-head-custom semi-timeline-item-head-default" aria-hidden="true">
                          <span className="Polaris-Icon Polaris-Icon--toneBase">
                            <svg viewBox="0 0 20 20" className="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                              <path d="M10.75 6a.75.75 0 0 0-1.5 0v4c0 .199.079.39.22.53l2 2a.75.75 0 1 0 1.06-1.06l-1.78-1.78v-3.69Z"></path>
                              <path
                                fill-rule="evenodd"
                                d="M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Zm-1.5 0a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0Z"
                              ></path>
                            </svg>
                          </span>
                        </div>
                        <div className="semi-timeline-item-content">
                          <p className="Polaris-Text--root Polaris-Text--bodyMd">
                            No information yet, tracking details will be available once updates are provided by the carriers.
                          </p>
                        </div>
                      </li>
                    )}
                    <li className="relative m-0 p-0 pb-6 list-none semi-timeline-item">
                      <div className="semi-timeline-item-tail" aria-hidden="true"></div>
                      <div className="semi-timeline-item-head semi-timeline-item-head-custom semi-timeline-item-head-default" aria-hidden="true">
                        <span className="Polaris-Icon Polaris-Icon--toneBase">
                          <svg viewBox="0 0 20 20" className="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                            <path
                              fill-rule="evenodd"
                              d="M7 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-4Zm.5 3.5v-2h3v2h-3Z"
                            ></path>
                            <path
                              fill-rule="evenodd"
                              d="M5.315 4.45a2.25 2.25 0 0 1 1.836-.95h5.796a2.25 2.25 0 0 1 1.872 1.002l1.22 1.828c.3.452.461.983.461 1.526v6.894a1.75 1.75 0 0 1-1.75 1.75h-9.5a1.75 1.75 0 0 1-1.75-1.75v-6.863c0-.57.177-1.125.506-1.59l1.309-1.848Zm1.836.55a.75.75 0 0 0-.612.316l-.839 1.184h3.55v-1.5h-2.1Zm3.599 1.5h3.599l-.778-1.166a.75.75 0 0 0-.624-.334h-2.197v1.5Zm4.25 1.5h-10v6.75c0 .138.112.25.25.25h9.5a.25.25 0 0 0 .25-.25v-6.75Z"
                            ></path>
                          </svg>
                        </span>
                      </div>
                      <div className="semi-timeline-item-content">
                        <p className="Polaris-Text--root Polaris-Text--bodyMd">Fulfilled</p>
                        <div className="semi-timeline-item-content-time">
                          <p className="Polaris-Text--root Polaris-Text--bodyMd Polaris-Text--subdued">{formatDate(fulfillmentData?.created_at)}</p>
                        </div>
                      </div>
                    </li>
                    <li className="relative m-0 p-0 pb-6 list-none semi-timeline-item">
                      <div className="semi-timeline-item-head semi-timeline-item-head-custom semi-timeline-item-head-default" aria-hidden="true">
                        <span className="Polaris-Icon Polaris-Icon--toneBase">
                          <svg viewBox="0 0 20 20" className="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                            <path
                              fill-rule="evenodd"
                              d="M9.621 4h5.258c.395 0 .736 0 1.017.023.297.024.592.078.875.222.424.216.768.56.984.984.144.283.198.578.222.875.023.28.023.622.023 1.017v2.258c0 .395 0 .736-.023 1.017a2.29 2.29 0 0 1-.222.875 2.25 2.25 0 0 1-.983.984c-.284.144-.58.198-.876.222-.28.023-.622.023-1.017.023h-2.58c-.08.083-.169.16-.265.23l-2.73 1.965c-.565.407-.93.67-1.335.859-.358.167-.736.29-1.125.363-.44.083-.889.083-1.586.083h-1.508a.75.75 0 0 1 0-1.5h1.436c.794 0 1.095-.003 1.379-.057.266-.05.524-.134.77-.248.261-.122.508-.296 1.152-.76l2.67-1.923a.423.423 0 0 0-.35-.753l-4.875 1.219a.75.75 0 0 1-.364-1.456l.932-.233v-2.289c-.59.002-.821.011-1.033.062a2.25 2.25 0 0 0-.65.27c-.21.128-.398.31-.943.854l-.594.594a.75.75 0 0 1-1.06-1.06l.654-.655c.46-.46.78-.78 1.16-1.012a3.75 3.75 0 0 1 1.083-.45c.397-.095.813-.103 1.387-.103a6.79 6.79 0 0 1 .019-.396 2.29 2.29 0 0 1 .222-.875 2.25 2.25 0 0 1 .984-.984 2.29 2.29 0 0 1 .875-.222c.28-.023.622-.023 1.017-.023Zm5.229 7h-2.024a1.925 1.925 0 0 0-2.382-1.697l-2.444.611v-1.414h8.5v.85c0 .432 0 .712-.018.924-.017.204-.045.28-.064.317a.75.75 0 0 1-.328.327c-.037.02-.112.047-.316.064-.212.017-.492.018-.924.018Zm1.645-4.5h-8.49c.002-.104.006-.194.013-.274.017-.204.045-.28.064-.316a.75.75 0 0 1 .328-.328c.037-.02.112-.047.316-.064.212-.017.492-.018.924-.018h5.2c.432 0 .712 0 .924.018.204.017.28.045.316.064a.75.75 0 0 1 .328.328c.02.037.047.112.064.316.007.08.01.17.013.274Z"
                            ></path>
                          </svg>
                        </span>
                      </div>
                      <div className="semi-timeline-item-content">
                        <p className="Polaris-Text--root Polaris-Text--bodyMd">Ordered</p>
                        <div className="semi-timeline-item-content-time">
                          <p className="Polaris-Text--root Polaris-Text--bodyMd Polaris-Text--subdued">
                            {formatDate(fulfillmentData?.order?.created_at)}
                          </p>
                        </div>
                      </div>
                    </li>
                  </ul>
                </Box>
              </BlockStack>
            </Card>
          </div>
        </Layout.Section>
        <Layout.Section variant="oneThird">
          <BlockStack gap={"400"}>
            <Card>
              <BlockStack gap={"300"}>
                <Text as="h3" variant="headingSm">
                  Courier
                </Text>

                <InlineStack blockAlign="center" wrap={false}>

                  <div
                    // className="items-center w-full gap-2"
                    style={{
                      // display: "grid",
                      width:"100%",
                      // gridTemplateColumns: "auto 1fr",
                    }}
                  >
                    {/*<Thumbnail size="small" source={carrierDetail?.picture} alt="Black choker necklace" />*/}
                    <BlockStack align="space-between">
                      <InlineStack align="space-between" blockAlign="center">
                        <Text as="h3" variant="headingSm">
                          {fulfillmentData?.tracking_company}
                        </Text>
                      </InlineStack>
                      <InlineStack align="space-between" blockAlign="center">
                        {/*<Link removeUnderline monochrome url={`/shipments/${id}`} target="_blank">*/}
                          <Text as="span" variant="headingSm" fontWeight="regular">
                            {fulfillmentData?.tracking_number}
                          </Text>
                        {/*</Link>*/}
                        <Button
                          onClick={() => copyToClipboard(fulfillmentData?.tracking_number)}
                          size="medium"
                          textAlign="center"
                          variant="plain"
                          icon={
                            <Tooltip content="Copy">
                              <Icon tone="subdued" source={ClipboardIcon} />
                            </Tooltip>
                          }
                        ></Button>
                      </InlineStack>
                    </BlockStack>
                  </div>
                </InlineStack>
                <BlockStack gap={"100"}>
                  <Text as="h3" variant="headingSm">
                    Shipment details
                  </Text>
                  <BlockStack gap={"100"}>
                    <InlineStack align="space-between" blockAlign="center">
                      <Text tone="subdued">Orders</Text>
                      <Link removeUnderline url={`https://${shop}/admin/orders/${fulfillmentData?.order?.shopify_order_id}`} target="_blank">
                        <Text>{fulfillmentData?.order?.name}</Text>
                      </Link>
                    </InlineStack>
                    <InlineStack align="space-between" blockAlign="center">
                      <Text tone="subdued">Transit time</Text>
                      <Text tone="subdued">{transitDays} days</Text>
                    </InlineStack>
                  </BlockStack>
                </BlockStack>
              </BlockStack>
            </Card>
            <Card>
              <BlockStack gap={"300"}>
                <Text as="h3" variant="headingSm">
                  Package
                </Text>
                <Box borderColor="border-secondary" borderRadius="200" borderWidth="025" borderStyle="solid">
                  {lineItems?.map((item, index) => (
                    <div key={index} className="product-item-wrapper">
                      <Box padding={"300"}>
                        <InlineStack align="space-between" wrap={false} gap={"400"}>
                          <div className="product-content flex gap-3">
                            <div className="product-img h-fit">
                              <Thumbnail size="small" source={item?.image} />
                            </div>
                            <div
                              style={{
                                flex: "1 1 0%",
                              }}
                            >
                              <BlockStack align="space-between" gap={"050"}>
                                <Text as="span" variant="bodyMd" fontWeight="medium">
                                  {item?.title}
                                </Text>
                              </BlockStack>
                            </div>
                          </div>
                          <Text tone="subdued">X{item?.quantity}</Text>
                        </InlineStack>
                      </Box>
                      {index == lineItems?.length - 1 ? "" : <Divider borderWidth="025" borderColor="border-secondary" />}
                    </div>
                  ))}
                </Box>
              </BlockStack>
            </Card>
            <Card>
              <BlockStack gap={"300"}>
                <BlockStack gap={"100"}>
                  <Text as="h3" variant="headingSm">
                    Customer
                  </Text>
                  <Link removeUnderline target="_blank" url="">
                    <Text>{`${customerDetails?.first_name} ${customerDetails?.last_name}`}</Text>
                  </Link>
                </BlockStack>
                <BlockStack gap={"100"}>
                  <Text as="h3" variant="headingSm">
                    Contact infomation
                  </Text>
                  <BlockStack gap={"100"}>
                    <InlineStack align="space-between" blockAlign="center">
                      <Text tone="subdued">{customerDetails?.email}</Text>
                      <Button
                        onClick={() => copyToClipboard(customerDetails?.email)}
                        size="medium"
                        textAlign="center"
                        variant="plain"
                        icon={
                          <Tooltip content="Copy">
                            <Icon tone="subdued" source={ClipboardIcon} />
                          </Tooltip>
                        }
                      ></Button>
                    </InlineStack>
                    <Text tone="subdued">{shippingAddress?.phone}</Text>
                  </BlockStack>
                </BlockStack>
                <BlockStack gap={"100"}>
                  <Text as="h3" variant="headingSm">
                    Shipping address
                  </Text>
                  <BlockStack gap={"100"}>
                    <InlineStack align="space-between" blockAlign="center">
                      <Text tone="subdued">{shippingAddress?.address1}</Text>
                      <Button
                        onClick={() =>
                          copyToClipboard(
                            `${shippingAddress?.address1 || ""}
                          ${shippingAddress?.address2 || ""}
                          ${shippingAddress?.zip || ""}
                          ${shippingAddress?.city || ""}
                          ${shippingAddress?.company || ""}
                          ${shippingAddress?.province || ""}
                          ${shippingAddress?.country || ""}`,
                          )
                        }
                        size="medium"
                        textAlign="center"
                        variant="plain"
                        icon={
                          <Tooltip content="Copy">
                            <Icon tone="subdued" source={ClipboardIcon} />
                          </Tooltip>
                        }
                      ></Button>
                    </InlineStack>
                    <Text tone="subdued">{shippingAddress?.address2}</Text>
                    <Text tone="subdued">{shippingAddress?.zip}</Text>
                    <Text tone="subdued">{shippingAddress?.city}</Text>
                    <Text tone="subdued">{shippingAddress?.company}</Text>
                    <Text tone="subdued">{shippingAddress?.province}</Text>
                    <Text tone="subdued">{shippingAddress?.country}</Text>
                  </BlockStack>
                </BlockStack>
              </BlockStack>
            </Card>
          </BlockStack>
        </Layout.Section>
        <Layout.Section></Layout.Section>
      </Layout>
      {toastErrorMsg}
      {toastSuccessMsg}
    </Page>
  );
}
