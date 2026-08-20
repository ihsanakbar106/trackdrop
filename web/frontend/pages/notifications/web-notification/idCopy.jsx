import { useAppBridge, useNavigate } from "@shopify/app-bridge-react";
import React, { useCallback, useContext, useEffect, useRef, useState } from "react";
import { useLocation } from "react-router-dom";
import { AppContext } from "../../../components";
import {
  ActionList,
  BlockStack,
  Box,
  Button,
  Card,
  DropZone,
  InlineStack,
  Layout,
  LegacyStack,
  Page,
  Popover,
  SkeletonBodyText,
  SkeletonDisplayText,
  SkeletonThumbnail,
  Spinner,
  Tabs,
  Text,
  TextField,
  Thumbnail,
  Toast,
} from "@shopify/polaris";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import { NoteIcon } from "@shopify/polaris-icons";

import MacNotificationbar from "../../../assets/MacNotificationbar.png";

function convertNumberToBoolean(value) {
  let booleanValue;
  if (value === 1) {
    booleanValue = true;
  } else {
    booleanValue = false;
  }
  return booleanValue;
}

const getFileSrc = (file) => {
  if (file && typeof file === "string" && file.startsWith("https")) {
    return file;
  } else if (file instanceof File) {
    return window.URL.createObjectURL(file);
  } else {
    return "https://via.placeholder.com/50";
  }
};

export default function WebNotification() {
  const navigate = useNavigate();
  const appBridge = useAppBridge();
  const emailSubjectDivRef = useRef(null);
  const titleDivRef = useRef(null);
  const location = useLocation();
  const ParamsId = location?.pathname?.split("/").pop();
  const { apiUrl, appUrl } = useContext(AppContext);

  // Loading states
  const [loading, setLoading] = useState(true);
  const [btnLoading, setBtnLoading] = useState(false);

  const initialState = {
    url: "https://trackify-app-testing-v16.myshopify.com",
    title: "Last chance for 25% off Everything!",
    message: "25% off Sale Ending Soon",
  };
  const [file, setFile] = useState();

  console.log("file", file);

  const [data, setData] = useState(initialState);
  const [webData, setWebData] = useState("");
  const [activeStatus, setActiveStatus] = useState("");
  const [selected, setSelected] = useState(0);
  const [popoverActiveVariable, setPopoverActiveVariable] = useState(false);
  const [popoverActiveTitleVariable, setPopoverActiveTitleVariable] = useState(false);

  // Toast notifications
  const [errorToast, setErrorToast] = useState(false);
  const [successToast, setSuccessToast] = useState(false);
  const [toastMsg, setToastMsg] = useState("");

  const tabs = [
    {
      id: "window-1",
      content: "Windows",
      panelID: "window-content-1",
    },
    {
      id: "macOS-1",
      content: "MacOS",
      panelID: "macOS-content-1",
    },
    {
      id: "mobile-1",
      content: "Mobile",
      panelID: "mobile-content-1",
    },
  ];

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}web-notification-detail/2`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { web_notification_data } = response?.data;
      const data = JSON.parse(web_notification_data?.data) || "";

      console.log("datadata", data);
      setWebData(web_notification_data);
      setData(data || initialState);
      setFile(web_notification_data?.logo ? `${appUrl}${web_notification_data?.logo}` : "");
      setActiveStatus(web_notification_data?.active_status);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, [ParamsId]);

  const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
  const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

  const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

  const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

  const handleCheckboxChangeIsActive = (currentStatus) => {
    setActiveStatus(currentStatus == 0 ? 1 : 0);
  };

  const handleChangeValue = useCallback((field, value) => {
    setData((prevState) => ({
      ...prevState,
      [field]: value,
    }));
  }, []);

  const togglePopoverActiveVariable = useCallback(() => setPopoverActiveVariable((popoverActiveVariable) => !popoverActiveVariable), []);
  const togglePopoverActiveTitleVariable = useCallback(
    () => setPopoverActiveTitleVariable((popoverActiveTitleVariable) => !popoverActiveTitleVariable),
    [],
  );

  const insertVariable = (variable) => {
    const textField = emailSubjectDivRef.current.querySelector("textarea");
    if (!textField) return;

    const { selectionStart, selectionEnd, value } = textField;
    const newValue = `${value.substring(0, selectionStart)}${variable}${value.substring(selectionEnd)}`;
    handleChangeValue("message", newValue);
    setPopoverActiveVariable(false);
  };
  const insertTitleVariable = (variable) => {
    const textField = titleDivRef.current.querySelector("textarea");
    if (!textField) return;

    const { selectionStart, selectionEnd, value } = textField;
    const newValue = `${value.substring(0, selectionStart)}${variable}${value.substring(selectionEnd)}`;
    handleChangeValue("title", newValue);
    setPopoverActiveTitleVariable(false);
  };

  const validImageTypes = ["image/gif", "image/jpeg", "image/png"];

  const fileUpload = !file && <DropZone.FileUpload actionTitle="Add image" actionHint="Accepts .jpg, .png, .gif, .jpeg." />;
  const uploadedFile = file && (
    <LegacyStack>
      <Thumbnail size="small" alt={file?.name} source={getFileSrc(file)} />
      <div>
        {file.name}
        <Text variant="bodySm" as="p">
          {file.size} bytes
        </Text>
      </div>
    </LegacyStack>
  );

  const handleDropZoneDrop = useCallback((_dropFiles, acceptedFiles, _rejectedFiles) => setFile(acceptedFiles[0]), []);

  const handleRemoveIcon = useCallback(() => {
    setFile("");
  }, []);

  const handleTabChange = useCallback((selectedTabIndex) => setSelected(selectedTabIndex), []);

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
        data: data,
        logo: file,
        active_status: activeStatus,
      };

      const response = await axios.post(`${apiUrl}web-notification-save/${ParamsId}`, payload, {
        headers: {
          "Content-Type": "multipart/form-data",
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
      fullWidth
      backAction={{
        content: "Notification",
        onAction: () => navigate(`/notifications/${webData?.notification_id}`),
      }}
      title={webData?.title}
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
      <Layout>
        <Layout.Section variant="oneHalf">
          <Card>
            <BlockStack gap={"400"}>
              {loading ? (
                <SkeletonDisplayText size="small" />
              ) : (
                <Text as="h2" variant="headingMd">
                  Basic Details
                </Text>
              )}
              <BlockStack gap={"300"}>
                {loading ? (
                  <>
                    <SkeletonDisplayText maxWidth="100%" />
                    <SkeletonDisplayText maxWidth="100%" />
                    <SkeletonDisplayText maxWidth="100%" />
                    <SkeletonDisplayText maxWidth="100%" />
                    <SkeletonDisplayText maxWidth="100%" />
                    <SkeletonDisplayText maxWidth="100%" />
                    {/* <SkeletonDisplayText maxWidth="100%" /> */}
                    <SkeletonThumbnail size="large" />
                  </>
                ) : (
                  <>
                    <TextField label="URl" value={data?.url} onChange={(value) => handleChangeValue("url", value)} autoComplete="off" />
                    <div ref={titleDivRef}>
                      <TextField
                        label="Title"
                        labelAction={{
                          content: (
                            <Popover
                              active={popoverActiveTitleVariable}
                              activator={
                                <Button onClick={togglePopoverActiveTitleVariable} variant="plain" size="medium" textAlign="center" disclosure>
                                  Insert variables
                                </Button>
                              }
                              autofocusTarget="first-node"
                              onClose={togglePopoverActiveTitleVariable}
                            >
                              <Popover.Pane>
                                <ActionList
                                  actionRole="menuitem"
                                  items={[
                                    {
                                      content: "product title",
                                      onAction: () => insertTitleVariable("{{product_title}}"),
                                    },
                                    {
                                      content: "store name",
                                      onAction: () => insertTitleVariable("{{store_name}}"),
                                    },
                                    {
                                      content: "tracking number",
                                      onAction: () => insertTitleVariable("{{tracking_number}}"),
                                    },
                                    {
                                      content: "carrier name",
                                      onAction: () => insertTitleVariable("{{carrier_name}}"),
                                    },
                                    {
                                      content: "carrier contact",
                                      onAction: () => insertTitleVariable("{{carrier_contact}}"),
                                    },
                                    {
                                      content: "dest. carrier name",
                                      onAction: () => insertTitleVariable("{{dest_carrier_name}}"),
                                    },
                                    {
                                      content: "dest. contact NO",
                                      onAction: () => insertTitleVariable("{{dest_contact_no}}"),
                                    },
                                    {
                                      content: "shipment status",
                                      onAction: () => insertTitleVariable("{{shipment_status}}"),
                                    },
                                    {
                                      content: "latest tracking info",
                                      onAction: () => insertTitleVariable("{{latest_tracking_info}}"),
                                    },
                                    {
                                      content: "latest update time",
                                      onAction: () => insertTitleVariable("{{latest_update_time}}"),
                                    },
                                    {
                                      content: "tracking link",
                                      onAction: () => insertTitleVariable("{{tracking_link}}"),
                                    },
                                    {
                                      content: "transit time",
                                      onAction: () => insertTitleVariable("{{transit_time}}"),
                                    },
                                    {
                                      content: "customer first name",
                                      onAction: () => insertTitleVariable("{{customer_first_name}}"),
                                    },
                                    {
                                      content: "customer full name",
                                      onAction: () => insertTitleVariable("{{customer_full_name}}"),
                                    },
                                    {
                                      content: "order ID",
                                      onAction: () => insertTitleVariable("{{order_id}}"),
                                    },
                                    {
                                      content: "comment",
                                      onAction: () => insertTitleVariable("{{comment}}"),
                                    },
                                    {
                                      content: "estimated delivery date",
                                      onAction: () => insertTitleVariable("{{estimated_delivery_date}}"),
                                    },
                                  ]}
                                />
                              </Popover.Pane>
                            </Popover>
                          ),
                        }}
                        value={data?.title}
                        onChange={(value) => handleChangeValue("title", value)}
                        autoComplete="off"
                      />
                    </div>
                    <div ref={emailSubjectDivRef}>
                      <TextField
                        label="Message"
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
                        value={data?.message}
                        onChange={(value) => handleChangeValue("message", value)}
                        multiline={4}
                        autoComplete="off"
                      />
                    </div>
                    {file ? (
                      <InlineStack blockAlign="center" align="space-between">
                        <Thumbnail
                          size="large"
                          alt={file.name}
                          source={getFileSrc(file)}
                          // source={validImageTypes.includes(file.type) ? window.URL.createObjectURL(file) : NoteIcon}
                        />
                        <Button onClick={handleRemoveIcon} variant="tertiary" size="medium" textAlign="center">
                          Remove
                        </Button>
                      </InlineStack>
                    ) : (
                      <DropZone label="Icon" allowMultiple={false} onDrop={handleDropZoneDrop}>
                        {uploadedFile}
                        {fileUpload}
                      </DropZone>
                    )}
                  </>
                )}
              </BlockStack>
            </BlockStack>
          </Card>
        </Layout.Section>
        <Layout.Section variant="oneThird">
          <Card padding={"0"}>
            <Tabs tabs={tabs} selected={selected} onSelect={handleTabChange} fitted />
            <Box paddingInlineStart={"400"} paddingInlineEnd={"400"} paddingBlockEnd={"400"} paddingBlockStart={"300"}>
              {selected == 0 ? (
                <>
                  <div className="preview_flyout_widget window_preveiew_main w-100 pb-[40px]">
                    <div className="window_preveiew flex justify-center items-end">
                      <div class="window_pre_btm_main">
                        <div class="window_pre_btm preview flex items-center">
                          <div class="close" id="content_close">
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path
                                d="M6.82529 5.97724L4.8454 3.99734L6.82034 2.02239C6.93287 1.90987 6.99608 1.75725 6.99608 1.59812C6.99608 1.43899 6.93287 1.28638 6.82034 1.17386C6.70782 1.06134 6.55521 0.998123 6.39608 0.998123C6.23695 0.998123 6.08434 1.06134 5.97182 1.17386L3.99687 3.14881L2.02545 1.1774C1.97158 1.1185 1.90603 1.07147 1.83299 1.03929C1.75995 1.00712 1.68101 0.990499 1.60119 0.990499C1.52137 0.990499 1.44243 1.00712 1.36939 1.03929C1.29634 1.07147 1.2308 1.1185 1.17692 1.1774C1.11883 1.23174 1.07252 1.29744 1.04086 1.37042C1.0092 1.4434 0.992865 1.52211 0.992865 1.60166C0.992865 1.68121 1.0092 1.75991 1.04086 1.83289C1.07252 1.90587 1.11883 1.97157 1.17693 2.02592L3.15187 4.00087L1.17551 5.97724C1.11742 6.03158 1.07111 6.09729 1.03945 6.17027C1.00779 6.24324 0.99145 6.32195 0.99145 6.4015C0.99145 6.48105 1.00779 6.55976 1.03945 6.63273C1.07111 6.70571 1.11742 6.77141 1.17551 6.82576C1.23123 6.88148 1.29737 6.92568 1.37016 6.95583C1.44296 6.98598 1.52098 7.0015 1.59977 7.0015C1.67857 7.0015 1.75659 6.98598 1.82939 6.95583C1.90218 6.92568 1.96832 6.88148 2.02404 6.82576L4.00394 4.84586L5.9803 6.82223C6.09282 6.93475 6.24544 6.99796 6.40457 6.99796C6.5637 6.99796 6.71631 6.93475 6.82883 6.82223C6.94135 6.70971 7.00457 6.55709 7.00457 6.39796C7.00457 6.23883 6.94135 6.08622 6.82883 5.9737L6.82529 5.97724Z"
                                fill="#3C3C3C"
                              ></path>
                            </svg>
                          </div>
                          {loading ? (
                            <SkeletonThumbnail size="medium" />
                          ) : (
                            <div class="pre_btm_img">
                              <img class="img-logo" src={getFileSrc(file)} alt="Uploaded Image" />
                            </div>
                          )}
                          <div class="body pre_btn_body">
                            <h6 class="small">{data?.title}</h6>
                            <p class="small">{data.message}</p>
                            <p class="text-xss mb-0">{data.url}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </>
              ) : selected == 1 ? (
                <>
                  <div className="preview_flyout_widget w-100 p-0">
                    <div className="macos_custom_main">
                      <div className="preview macos_custom_preview">
                        <div className="macos_custom_preview_item flex items-center">
                          <div class="macos_img">
                            <img
                              id="uploadedImage2"
                              class="img-logo"
                              uploadedimage2=""
                              src={getFileSrc(file)}
                              alt="Uploaded Image"
                              accept="image/png, image/jpeg"
                            />
                          </div>
                          <div class="body">
                            <h6 class="small mb-1" ab_cart_n_title="">
                              <b>{data.title}</b>
                            </h6>
                            <p class="small mb-2" ab_cart_n_message="">
                              {data.message}
                            </p>
                            <p ab_cart_n_url="" class="text-xss mb-0">
                              {data.url}
                            </p>
                          </div>
                          <div class="right_btn_grp ms-auto">
                            <a class="close" href="#">
                              Close
                            </a>
                            <a class="setting" href="#">
                              Setting
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </>
              ) : (
                <div className="preview_flyout_widget w-100 p-1 pb-0 pt-3 md:pt-5">
                  <div className="flex justify-center">
                    <div className="preview mobile_custom_preview">
                      <div class="mobile_custom_preview_in">
                        <div className="flex items-center">
                          <p class="text-xss mobile_pre_url">
                            <svg
                              width="12"
                              height="18"
                              class="svg-inline--fa fa-bell fa-w-14"
                              aria-hidden="true"
                              focusable="false"
                              data-prefix="fa"
                              data-icon="bell"
                              role="img"
                              xmlns="http://www.w3.org/2000/svg"
                              viewBox="0 0 448 512"
                              data-fa-i2svg=""
                            >
                              <path
                                fill="currentColor"
                                d="M224 512c35.32 0 63.97-28.65 63.97-64H160.03c0 35.35 28.65 64 63.97 64zm215.39-149.71c-19.32-20.76-55.47-51.99-55.47-154.29 0-77.7-54.48-139.9-127.94-155.16V32c0-17.67-14.32-32-31.98-32s-31.98 14.33-31.98 32v20.84C118.56 68.1 64.08 130.3 64.08 208c0 102.3-36.15 133.53-55.47 154.29-6 6.45-8.66 14.16-8.61 21.71.11 16.4 12.98 32 32.1 32h383.8c19.12 0 32-15.6 32.1-32 .05-7.55-2.61-15.27-8.61-21.71z"
                              ></path>
                            </svg>
                            {data.url}
                          </p>
                        </div>
                        <div class="flex justify-between items-center">
                          <div class="body pe-4">
                            <h6 class="small mobile_pre_title">
                              <b>{data?.title || "Push Title"}</b>
                            </h6>
                            <p class="small mb-0 mobile_pre_subtitle">{data?.message || "Push Body"}</p>
                          </div>
                          <div class="m-0">
                            <img class="img-logo" src={getFileSrc(file)} alt="Uploaded Image" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </Box>
          </Card>
        </Layout.Section>
      </Layout>
      {toastErrorMsg}
      {toastSuccessMsg}
    </Page>
  );
}
