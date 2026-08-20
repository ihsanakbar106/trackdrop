import { useAppBridge, useNavigate } from "@shopify/app-bridge-react";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import {
  ActionList,
  Badge,
  BlockStack,
  Box,
  Button,
  ButtonGroup,
  Checkbox,
  ColorPicker,
  Divider,
  FullscreenBar,
  Icon,
  InlineGrid,
  InlineStack,
  Label,
  Modal,
  Page,
  Popover,
  Scrollable,
  Select,
  SkeletonBodyText,
  SkeletonDisplayText,
  SkeletonThumbnail,
  Spinner,
  Text,
  TextField,
  Thumbnail,
  Toast,
} from "@shopify/polaris";
import {
  ButtonPressIcon,
  CartIcon,
  ChevronLeftIcon,
  CollectionFeaturedIcon,
  DesktopIcon,
  DiscountIcon,
  EmailIcon,
  HideIcon,
  IconsIcon,
  LayoutFooterIcon,
  LayoutSectionIcon,
  MobileIcon,
  SearchIcon,
  SettingsIcon,
  ViewIcon,
} from "@shopify/polaris-icons";
import React, { useCallback, useContext, useEffect, useRef, useState } from "react";
import { useLocation } from "react-router-dom";
import { AppContext } from "../../../components";
import axios from "axios";
import BrandingCustomizer from "../../../components/BrandingCustomizer";
import tinycolor from "tinycolor2";
import TrackingButton from "../../../components/TrackingButton";
import AdditionalDetails from "../../../components/AdditionalDetails";
import LineItemsCustomizer from "../../../components/LineItemsCustomizer";
import FeaturedProductsCustomizer from "../../../components/FeaturedProductsCustomizer";
import FooterCustomizer from "../../../components/FooterCustomizer";
import NoneBackground from "../../../assets/NoneBackground.png";
import { CKEditor } from "@ckeditor/ckeditor5-react";
// import InlineEditor from "@ckeditor/ckeditor5-build-inline";
import {
  InlineEditor,
  AccessibilityHelp,
  Alignment,
  AutoLink,
  Autosave,
  Bold,
  Essentials,
  FontBackgroundColor,
  FontColor,
  FontFamily,
  FontSize,
  Heading,
  Indent,
  IndentBlock,
  Italic,
  Underline,
  Link,
  Paragraph,
  SelectAll,
  SpecialCharacters,
  Undo,
  List,
  ClassicEditor,
} from "ckeditor5";
import Placeholder from "../../../components/providers/placeholder/placeholder";
import "../../../assets/ckeditorStyles/ckeditor5-content.css";
import "../../../assets/ckeditorStyles/ckeditor5-editor.css";
import "../../../assets/ckeditorStyles/ckeditor5.css";

import "../../../custom.css";

function convertNumberToBoolean(value) {
  let booleanValue;
  if (value === 1) {
    booleanValue = true;
  } else {
    booleanValue = false;
  }
  return booleanValue;
}

export default function Template() {
  const navigate = useNavigate();
  const appBridge = useAppBridge();
  const location = useLocation();
  const emailSubjectDivRef = useRef(null);
  const ParamsId = location?.pathname?.split("/").pop();
  const { apiUrl } = useContext(AppContext);
  // Loading states
  const [loading, setLoading] = useState(true);
  const [btnLoading, setBtnLoading] = useState(false);
  const [productsLoading, setProductsLoading] = useState(true);
  const [toggleLoadProducts, setToggleLoadProducts] = useState(false);

  // Toast notifications
  const [errorToast, setErrorToast] = useState(false);
  const [successToast, setSuccessToast] = useState(false);
  const [toastMsg, setToastMsg] = useState("");

  // Sidebar list and state tracking
  const initialList = [
    { title: "Email Subject", visible: true },
    { title: "Branding", visible: true },
    { title: "Status description", visible: true },
    { title: "Tracking button", visible: true },
    { title: "Additional details", visible: true },
    { title: "Line items", visible: true },
    { title: "Featured products", visible: true },
    { title: "Footer", visible: true },
  ];
  const [sidebarList, setSidebarList] = useState(initialList);
  const [isChanged, setIsChanged] = useState(false);

  // Email and notification states
  const [emailData, setEmailData] = useState("");
  const [emailsList, setEmailsList] = useState([]);
  const [activeStatus, setActiveStatus] = useState("");
  const [notificationType, setNotificationType] = useState("");

  // View and setting states
  const [view, setView] = useState("Desktop");
  const [setting, setSetting] = useState("Section");

  // Popover states
  const [popoverActive, setPopoverActive] = useState(false);
  const [popoverActiveVariable, setPopoverActiveVariable] = useState(false);
  const [popoverActiveStatusDescriptionBackgroundColor, setPopoverActiveStatusDescriptionBackgroundColor] = useState(false);
  const [popoverActiveStatusDescriptionTextColor, setPopoverActiveStatusDescriptionTextColor] = useState(false);
  const [popoverActiveThemeSettingPrimaryTextColor, setPopoverActiveThemeSettingPrimaryTextColor] = useState(false);
  const [popoverActiveThemeSettingPrimaryColor, setPopoverActiveThemeSettingPrimaryColor] = useState(false);
  const [popoverActiveThemeSettingBackgroundColor, setPopoverActiveThemeSettingBackgroundColor] = useState(false);

  const customizedataInitialList = {
    emailSubject: "",
    brandingType: ["Store name"],
    textColor: "f7f7f7",
    logo: "",
    imageWidth: "100",
    brandingBackgroundColor: "000000",
    paddingTop: "20",
    paddingBottom: "20",
    statusDescriptionBackgroundColor: "",
    statusDescriptionDetails: `<p style="text-align:center;">📦 Your order is on its way</p><p style="text-align:center;">Hi ,<span class="placeholder ck-widget" contenteditable="false">{first name}</span>,</p><p style="text-align:center;">your order has been dispatched and is now in transit. Thanks for shopping with us.</p>`,
    statusDescriptionTextColor: "000000",
    trackingButtonButtonText: "Track order status",
    trackingButtonLink: ["Tracking Link"],
    trackingButtonCustomizeLink: "",
    trackingButtonButtonWidth: "56",
    trackingButtonFontSize: "14",
    trackingButtonButtonColor: "000000",
    trackingButtonTextColor: "ffffff",
    trackingButtonAlignment: "Center",
    trackingButtonBackgroundColor: "",
    additionalDetailsBackgroundColor: "",
    additionalDetailsTextColor: "000000",
    additionalDetails: `<p style="text-align:center;">Tracking Number: {tracking_number}&nbsp;</p><p style="text-align:center;">Carrier: {carrier_name}&nbsp;</p><p style="text-align:center;">Order ID: {order_id}</p>`,
    lineItemTitleFontSize: "16",
    lineItemMainTitle: "",
    lineItemAmount: "",
    lineItemSubtitle: "",
    lineItemProductAmountHide: "0",
    lineItemCurrency: "",
    lineItemProductFontSize: "14",
    lineItemCurrencyAlignment: "Left",
    lineItemTitleColor: "",
    lineItemItemTextColor: "000000",
    lineItemBackgroundColor: "",
    featuredProductsMainTitle: "",
    featuredProductsTitleColor: "C4CDD5",
    featuredProductsItemTextColor: "000000",
    featuredProductsBackgroundColor: "",
    footerUnsubscribeButton: "1",
    footerUnsubscribe: "Unsubscribe",
    footerUnsubscribeButtonColor: "005BD3",
    footerTextColor: "616161",
    footerBackgroundColor: "ffffff",
    footerDetails: `<p style="text-align:center;">Unit 83, 3/F Yau Lee Ctr., No.45 Hoi Yuen Road, Kwun Tong Kln, HONG KONG</p><p style="text-align:center;">© 2022 TrackingMore</p>`,
    themeSettingFontFamily: "SF Pro Text",
    themeSettingPrimaryTextColor: "000000",
    themeSettingPrimaryColor: "007a5c",
    themeSettingBackgroundColor: "",
  };

  // Customize data state
  const [customizedata, setCustomizeData] = useState(customizedataInitialList);

  // Product states
  const [productsList, setProductsList] = useState([]);
  const [modalProductsList, setModalProductsList] = useState([]);
  const [textFieldValueSearch, setTextFieldValueSearch] = useState("");
  const [productModal, setProductModal] = useState(false);
  const [selectedProductsIDs, setSelectedProductsIDs] = useState([]);
  const [selectedProducts, setSelectedProducts] = useState([]);

  // Color picker state
  const [colorPicker, setColorPicker] = useState({
    alpha: 1,
    hue: 120,
    brightness: 1,
    saturation: 1,
  });

  console.log("customizedata", customizedata);

  const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
  const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

  const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

  const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

  const editorConfig = {
    toolbar: {
      items: [
        "heading",
        "|",
        "bold",
        "italic",
        "underline",
        "fontColor",
        "fontBackgroundColor",
        "alignment",
        "|",
        "link",
        "|",
        "bulletedList",
        "numberedList",
        "outdent",
        "indent",
        "placeholder",
      ],
      shouldNotGroupWhenFull: true,
    },

    // toolbar: {
    //   items: [
    //     "heading",
    //     "|",
    //     "bold",
    //     "italic",
    //     "underline",
    //     {
    //       label: "Color",
    //       icon: "text",
    //       items: ["fontColor", "fontBackgroundColor"],
    //     },
    //   ],
    //   shouldNotGroupWhenFull: true
    // },

    plugins: [
      AccessibilityHelp,
      Alignment,
      AutoLink,
      Autosave,
      Bold,
      Underline,
      Essentials,
      FontBackgroundColor,
      FontColor,
      FontFamily,
      FontSize,
      Heading,
      List,
      Indent,
      IndentBlock,
      Italic,
      Link,
      Paragraph,
      SelectAll,
      SpecialCharacters,
      Undo,
      Placeholder,
    ],
    fontFamily: {
      supportAllValues: true,
    },
    fontSize: {
      options: [10, 12, 14, "default", 18, 20, 22, 24, 26, 32],
      supportAllValues: true,
    },
    heading: {
      options: [
        {
          model: "paragraph",
          title: "Paragraph",
          class: "ck-heading_paragraph",
        },
        {
          model: "heading1",
          view: "h1",
          title: "Heading 1",
          class: "ck-heading_heading1",
        },
        {
          model: "heading2",
          view: "h2",
          title: "Heading 2",
          class: "ck-heading_heading2",
        },
        {
          model: "heading3",
          view: "h3",
          title: "Heading 3",
          class: "ck-heading_heading3",
        },
        {
          model: "heading4",
          view: "h4",
          title: "Heading 4",
          class: "ck-heading_heading4",
        },
        {
          model: "heading5",
          view: "h5",
          title: "Heading 5",
          class: "ck-heading_heading5",
        },
        {
          model: "heading6",
          view: "h6",
          title: "Heading 6",
          class: "ck-heading_heading6",
        },
      ],
    },
    link: {
      addTargetToExternalLinks: true,
      defaultProtocol: "https://",
      decorators: {
        toggleDownloadable: {
          mode: "manual",
          label: "Downloadable",
          attributes: {
            download: "file",
          },
        },
      },
    },
    placeholderConfig: {
      types: [
        {
          label: "product title",
          value: "product_title",
        },
        {
          label: "store name",
          value: "store_name",
        },
        {
          label: "tracking number",
          value: "tracking_number",
        },
        {
          label: "carrier name",
          value: "carrier_name",
        },
        {
          label: "carrier contact",
          value: "carrier_contact",
        },
        {
          label: "dest. carrier name",
          value: "dest_carrier_name",
        },
        {
          label: "dest. contact NO",
          value: "dest_contact_no",
        },
        {
          label: "shipment status",
          value: "shipment_status",
        },
        {
          label: "latest tracking info",
          value: "latest_tracking_info",
        },
        {
          label: "latest update time",
          value: "latest_update_time",
        },
        {
          label: "tracking link",
          value: "tracking_link",
        },
        {
          label: "transit time",
          value: "transit_time",
        },
        {
          label: "customer first name",
          value: "customer_first_name",
        },
        {
          label: "customer full name",
          value: "customer_full_name",
        },
        {
          label: "order ID",
          value: "order_id",
        },
        {
          label: "comment",
          value: "comment",
        },
        {
          label: "estimated delivery date",
          value: "estimated_delivery_date",
        },
      ], // ADDED
    },
  };

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}email-detail/${ParamsId}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { email_data } = response?.data;
      const data = JSON.parse(email_data.data) || "";
      console.log("datadatadata", data);

      setEmailData(email_data || "");
      setCustomizeData(data?.customizedata || customizedataInitialList);
      setSelectedProductsIDs(data?.selectedProductsIDs || []);
      setSelectedProducts(data?.selectedProducts || []);
      setActiveStatus(email_data?.active_status);
      setSidebarList(data?.sidebarList || initialList);

      // Update only the visibility statuses of sidebarList
      // setSidebarList((prevList) =>
      //   prevList.map((item) => {
      //     const updatedItem = data?.sidebarList?.find((apiItem) => apiItem.title === item.title);
      //     return updatedItem ? { ...item, visible: updatedItem.visible } : item;
      //   }),
      // );

      fetchEmailList(email_data?.notification_id);
    } catch (error) {
      console.error("Error fetching data:", error);
    }
  };

  useEffect(() => {
    fetchData();
  }, [ParamsId]);

  useEffect(() => {
    // Check if the current sidebarList is different from the initialList
    const isStateChanged = JSON.stringify(sidebarList) !== JSON.stringify(initialList);
    setIsChanged(isStateChanged);
  }, [sidebarList]);

  const fetchEmailList = async (id) => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}notification-detail/${id}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { emails, web_notifications, sms_notifications, notification_type } = response?.data;
      setEmailsList(emails || web_notifications || sms_notifications || []);
      setNotificationType(notification_type);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
    }
  };

  const fetchProductList = async () => {
    let sessionToken = await getSessionToken(appBridge);
    try {
      const response = await axios.get(`${apiUrl}product-list?search=${textFieldValueSearch}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      if (response?.status == 200) {
        const newProducts = response?.data?.products || [];
        setModalProductsList(newProducts);
        setProductsList((prevProducts) => {
          const allProducts = [...prevProducts, ...newProducts];
          const uniqueProducts = Array.from(new Set(allProducts.map((product) => product.id))).map((id) =>
            allProducts.find((product) => product.id === id),
          );
          return uniqueProducts;
        });
      }
    } catch (error) {
      setToggleLoadProducts(false);
      setProductsLoading(false);
    } finally {
      setToggleLoadProducts(false);
      setProductsLoading(false);
    }
  };

  useEffect(() => {
    if (toggleLoadProducts) {
      fetchProductList();
    }
  }, [toggleLoadProducts, textFieldValueSearch]);

  const handleTextFieldSearchChange = useCallback((value) => {
    setTextFieldValueSearch(value);
    setToggleLoadProducts(true);
  }, []);

  const handleOpenProductModal = useCallback(() => {
    setProductModal(true);
    setToggleLoadProducts(true);
  }, []);

  const handleCancelProductModal = useCallback(() => {
    setProductModal(false);
  }, []);

  const handleClearProduct = useCallback(() => {
    setSelectedProductsIDs([]);
    setSelectedProducts([]);
  }, []);

  const handleProductSelect = (id) => {
    const selectedCount = selectedProductsIDs?.length;

    // Check if the maximum allowed selection count (5) is reached
    if (selectedCount >= 4) {
      // If the selected product is already in the list, remove it
      if (selectedProductsIDs?.includes(id)) {
        const newArray = selectedProductsIDs?.filter((item) => item !== id);
        setSelectedProductsIDs(newArray);
      }
      // Otherwise, do nothing (don't add more items beyond the limit)
      return;
    }

    // If the selected product is already in the list, remove it
    if (selectedProductsIDs?.includes(id)) {
      const newArray = selectedProductsIDs?.filter((item) => item !== id);
      setSelectedProductsIDs(newArray);
    } else {
      // Add the selected product if the limit is not reached
      setSelectedProductsIDs([...selectedProductsIDs, id]);
    }
  };

  const handleProductsSaveModal = () => {
    setProductModal(false);
    const selectedProd = productsList?.filter((product) => selectedProductsIDs?.includes(product.shopify_product_id));
    setSelectedProducts(selectedProd);
  };

  const handleRemoveProduct = (productId) => {
    const newArray = selectedProducts?.filter((product) => product?.shopify_product_id !== productId);
    const newIdsArray = selectedProductsIDs?.filter((id) => id !== productId);
    setSelectedProducts(newArray);
    setSelectedProductsIDs(newIdsArray);
  };

  const togglePopoverActive = useCallback(() => setPopoverActive((popoverActive) => !popoverActive), []);

  const togglePopoverActiveVariable = useCallback(() => setPopoverActiveVariable((popoverActiveVariable) => !popoverActiveVariable), []);

  const handleSetting = useCallback((key) => setSetting(key), []);
  const handleView = useCallback((key) => setView(key), []);

  // Function to toggle visibility status
  const handleToggleVisibility = (index) => {
    setSidebarList((prevList) => prevList.map((item, idx) => (idx === index ? { ...item, visible: !item.visible } : item)));
  };

  const colorPickerFunc = (newColor, field) => {
    setColorPicker(newColor);
    const rgbaColor = tinycolor({
      h: newColor.hue,
      s: newColor.saturation,
      v: newColor.brightness,
      a: newColor.alpha,
    }).toRgb();
    const hexColor = tinycolor(rgbaColor).toHex8();
    setCustomizeData((prevState) => ({
      ...prevState,
      [field]: hexColor,
    }));
  };

  const insertVariable = (variable) => {
    const textField = emailSubjectDivRef.current.querySelector("textarea");
    if (!textField) return;

    const { selectionStart, selectionEnd, value } = textField;
    const newValue = `${value.substring(0, selectionStart)}${variable}${value.substring(selectionEnd)}`;
    handleChangeValue("emailSubject", newValue);
  };

  const handleChangeValue = useCallback((field, value) => {
    setCustomizeData((prevState) => ({
      ...prevState,
      [field]: value,
    }));
  }, []);

  const handleDropZoneDrop = useCallback(
    (_dropFiles, acceptedFiles, _rejectedFiles) =>
      setCustomizeData((prevState) => ({
        ...prevState,
        logo: acceptedFiles[0],
      })),
    [],
  );

  const handleRemoveLogo = useCallback(() => {
    setCustomizeData((prevState) => ({
      ...prevState,
      logo: "",
    }));
  }, []);

  const togglePopover = (popoverStateFunction) => () => popoverStateFunction((active) => !active);

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
        data: {
          customizedata: customizedata,
          sidebarList: sidebarList,
          selectedProductsIDs: selectedProductsIDs,
          selectedProducts: selectedProducts,
        },
        active_status: activeStatus,
      };

      const response = await axios.post(`${apiUrl}email-save/${ParamsId}`, payload, {
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
      <Modal
        open={productModal}
        size="fullScreen"
        onClose={handleCancelProductModal}
        title="Add products"
        primaryAction={{
          content: "Add",
          disabled: !selectedProductsIDs?.length,
          onAction: handleProductsSaveModal,
        }}
        secondaryActions={[
          {
            content: "Cancel",
            onAction: handleCancelProductModal,
          },
        ]}
      >
        <Box paddingBlockStart={3} paddingBlockEnd={3} paddingInlineStart={4} paddingInlineEnd={4}>
          <TextField
            labelHidden
            type="text"
            placeholder="Search products"
            value={textFieldValueSearch}
            prefix={<Icon source={SearchIcon} tone="base" />}
            onChange={handleTextFieldSearchChange}
            autoComplete="off"
          />
        </Box>
        <Divider borderWidth={1} />
        <div className="product-lists">
          <Scrollable horizontal vertical className="yr5fA CyBRb">
            {productsLoading ? (
              <div
                style={{
                  height: "100%",
                  display: "flex",
                  justifyContent: "center",
                  alignItems: "center",
                }}
              >
                <Spinner size="large" />
              </div>
            ) : modalProductsList?.length ? (
              modalProductsList?.map((product, i) => {
                const isSelectedId = selectedProductsIDs?.includes(product.shopify_product_id);
                const isCheckboxDisabled = selectedProductsIDs?.length >= 4 && !isSelectedId;
                return (
                  <div
                    className="product-list-item"
                    key={i}
                    style={{
                      backgroundColor: isCheckboxDisabled ? "var(--p-color-bg-surface-secondary)" : "unset",
                      color: isCheckboxDisabled ? "var(--p-color-text-disabled)" : "unset",
                    }}
                  >
                    <Checkbox
                      labelHidden
                      checked={isSelectedId}
                      disabled={isCheckboxDisabled}
                      onChange={() => handleProductSelect(product.shopify_product_id)}
                    />
                    <div className="product-list-item-product-title" onClick={() => handleProductSelect(product.shopify_product_id)}>
                      <div className="product-list-item-product-title-inner">
                        <div className="product-list-item-product-title-thumbnail">
                          <Thumbnail source={product?.image} size="small" />
                        </div>
                        <div className="product-list-item-product-title-text">
                          <div className="ExJYf">
                            <div className="K2zxu">
                              <span>{product?.title}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                );
              })
            ) : (
              <div
                style={{
                  height: "100%",
                  display: "flex",
                  justifyContent: "center",
                  alignItems: "center",
                }}
              >
                <Text as="h2" variant="headingMd">
                  No Product Found
                </Text>
              </div>
            )}
          </Scrollable>
        </div>
      </Modal>
      <div style={{ minHeight: "80vh" }}>
        <FullscreenBar onAction={() => navigate(`/notifications/${emailData?.notification_id}`)}>
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
                    <Text variant="headingMd">{emailData?.email_type}</Text>
                  </InlineStack>
                  <InlineStack blockAlign="center" wrap={false} gap={"400"}>
                    <Popover
                      active={popoverActive}
                      activator={
                        <Button onClick={togglePopoverActive} variant="tertiary" size="medium" textAlign="center" disclosure>
                          <Text as="span" variant="bodySm" fontWeight="medium">
                            {emailData?.email_type}
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
                            navigate(`/notifications/email-customize/${item?.id}`);
                            setPopoverActive((popoverActive) => !popoverActive);
                          },
                        }))}
                      />
                    </Popover>
                  </InlineStack>
                </>
              )}
              <InlineStack blockAlign="center" gap={"200"}>
                <Button size="medium" textAlign="center">
                  <Text as="span" variant="bodySm" fontWeight="medium">
                    Send Test
                  </Text>
                </Button>
                <ButtonGroup variant="segmented">
                  <Button size="medium" textAlign="center" pressed={view === "Desktop"} icon={DesktopIcon} onClick={() => handleView("Desktop")} />
                  <Button size="medium" textAlign="center" pressed={view === "Mobile"} icon={MobileIcon} onClick={() => handleView("Mobile")} />
                </ButtonGroup>
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
                {/* <Button size="medium" textAlign="center" disabled>
                  <Text as="span" variant="bodySm" fontWeight="medium">
                    Discard
                  </Text>
                </Button> */}

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
            {loading ? (
              <div className="border bg-white w-full max-w-[56px] shadow-inner flex flex-col p-3 gap-4">
                <SkeletonDisplayText size="small" />
                <SkeletonDisplayText size="small" />
              </div>
            ) : (
              <div className="border bg-white w-full max-w-[56px] shadow-inner justify-start items-start gap-2 inline-flex">
                <div className="inline-flex flex-col items-center self-stretch justify-start py-4 bg-white bg-opacity-0 grow shrink basis-0 customize-tab">
                  <BlockStack gap={"100"}>
                    <button
                      onClick={() => handleSetting("Section")}
                      class={`Polaris-Button Polaris-Button--pressable Polaris-Button--variantTertiary Polaris-Button--sizeMedium Polaris-Button--textAlignCenter ${
                        setting === "Section" && "active"
                      }`}
                      type="button"
                      aria-checked="true"
                    >
                      <Text as="span" variant="bodySm" fontWeight="medium">
                        <Icon tone="base" source={LayoutSectionIcon} />
                      </Text>
                    </button>
                    <button
                      onClick={() => handleSetting("Theme setting")}
                      class={`Polaris-Button Polaris-Button--pressable Polaris-Button--variantTertiary Polaris-Button--sizeMedium Polaris-Button--textAlignCenter ${
                        setting === "Theme setting" && "active"
                      }`}
                      type="button"
                      aria-checked="true"
                    >
                      <Text as="span" variant="bodySm" fontWeight="medium">
                        <Icon tone="base" source={SettingsIcon} />
                      </Text>
                    </button>
                  </BlockStack>
                </div>
              </div>
            )}
            <div
              className="relative bg-white border scroll_custom overflow-y-auto"
              style={{ width: "100%", maxWidth: "320px", height: "calc(-58px + 100vh)" }}
            >
              {setting == "Section" ? (
                <BlockStack>
                  <Box padding={"400"}>
                    {loading ? (
                      <SkeletonDisplayText size="small" />
                    ) : (
                      <Text as="h1" variant="headingMd">
                        Sections
                      </Text>
                    )}
                  </Box>
                  <Divider />
                  {loading ? (
                    <Box padding={"400"}>
                      <SkeletonBodyText lines={25} />
                    </Box>
                  ) : (
                    sidebarList?.map((item, index) => (
                      <React.Fragment key={index}>
                        <InlineStack blockAlign="center" wrap={false} gap={"100"}>
                          <div
                            onClick={() => handleSetting(item?.title)}
                            className="!justify-between !px-4 !py-3 !pr-3 !rounded-none Polaris-Button Polaris-Button--pressable Polaris-Button--variantTertiary Polaris-Button--textAlignLeft Polaris-Button--fullWidth"
                          >
                            <div>
                              <InlineStack blockAlign="center" wrap={false} gap={"100"}>
                                {item?.title === "Status description" || item?.title === "Additional details" ? (
                                  <span class="Polaris-Icon Polaris-Icon--toneBase">
                                    <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                                      <path d="M7.25 6.5a.75.75 0 0 0 0 1.5h5.5a.75.75 0 0 0 0-1.5h-5.5Z"></path>
                                      <path d="M6.5 10a.75.75 0 0 1 .75-.75h5.5a.75.75 0 0 1 0 1.5h-5.5a.75.75 0 0 1-.75-.75Z"></path>
                                      <path d="M7.25 12a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5h-3.5Z"></path>
                                      <path
                                        fillRule="evenodd"
                                        d="M7.25 3.5a3.75 3.75 0 0 0-3.75 3.75v5.5a3.75 3.75 0 0 0 3.75 3.75h5.5a3.75 3.75 0 0 0 3.75-3.75v-5.5a3.75 3.75 0 0 0-3.75-3.75h-5.5Zm-2.25 3.75a2.25 2.25 0 0 1 2.25-2.25h5.5a2.25 2.25 0 0 1 2.25 2.25v5.5a2.25 2.25 0 0 1-2.25 2.25h-5.5a2.25 2.25 0 0 1-2.25-2.25v-5.5Z"
                                      ></path>
                                    </svg>
                                  </span>
                                ) : item?.title === "Email Subject" ? (
                                  <Icon source={EmailIcon} />
                                ) : item?.title === "Branding" ? (
                                  <Icon source={IconsIcon} />
                                ) : item?.title === "Tracking button" ? (
                                  <Icon source={ButtonPressIcon} />
                                ) : item?.title === "Line items" ? (
                                  <Icon source={CartIcon} />
                                ) : item?.title === "Featured products" ? (
                                  <Icon source={CollectionFeaturedIcon} />
                                ) : item?.title === "Footer" ? (
                                  <Icon source={LayoutFooterIcon} />
                                ) : (
                                  ""
                                )}
                                <Text as="h3" variant="bodyMd">
                                  {item?.title}
                                </Text>
                              </InlineStack>
                            </div>
                            {item?.title !== "Email Subject" && (
                              <div className="flex items-center">
                                <Button
                                  variant="tertiary"
                                  size="medium"
                                  textAlign="center"
                                  icon={item.visible ? ViewIcon : HideIcon}
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    handleToggleVisibility(index);
                                  }}
                                ></Button>
                              </div>
                            )}
                          </div>
                        </InlineStack>
                        <Divider />
                      </React.Fragment>
                    ))
                  )}
                </BlockStack>
              ) : setting == "Theme setting" ? (
                <div className="h-full">
                  <Box padding={"400"}>
                    <Text as="h1" variant="headingMd">
                      Theme setting
                    </Text>
                  </Box>
                  <Divider />
                  <div className="flex flex-col gap-4 p-4 pb-16 overflow-y-auto scroll_custom" style={{ maxHeight: "calc(-116px + 100vh)" }}>
                    <Text fontWeight="medium">Trigger rule setting</Text>
                    <BlockStack gap={"300"}>
                      <Select
                        label="Tracking status"
                        options={emailsList?.map((item) => ({
                          label: item?.title,
                          value: item?.id,
                        }))}
                        disabled
                      />
                      <Select
                        label="Send as"
                        options={emailsList?.map((item) => ({
                          label: item?.title,
                          value: item?.id,
                        }))}
                        disabled
                        helpText="Emails with the same status will only be triggered once"
                      />
                    </BlockStack>
                    <Divider />
                    <Text fontWeight="medium">Global setting</Text>
                    <BlockStack gap={"300"}>
                      <Select
                        label="Font Family"
                        value={customizedata?.themeSettingFontFamily}
                        onChange={(value) => handleChangeValue("themeSettingFontFamily", value)}
                        options={[
                          { label: "SF Pro Text", value: "SF Pro Text" },
                          { label: "Arial", value: "Arial, Helvetica, sans-serif" },
                          { label: "Courier New", value: "'Courier New', Courier, monospace" },
                          { label: "Georgia", value: "Georgia, serif" },
                          {
                            label: "Lucida Sans Unicode",
                            value: "'Lucida Sans Unicode', 'Lucida Grande', sans-serif",
                          },
                          { label: "Tahoma", value: "Tahoma, Geneva, sans-serif" },
                          { label: "Times New Roman", value: "'Times New Roman', Times, serif" },
                          { label: "Trebuchet MS", value: "'Trebuchet MS', Helvetica, sans-serif" },
                          { label: "Verdana", value: "Verdana, Geneva, sans-serif" },
                        ]}
                      />
                      {colorField(
                        "Primary text color",
                        "themeSettingPrimaryTextColor",
                        customizedata?.themeSettingPrimaryTextColor,
                        handleChangeValue,
                        popoverActiveThemeSettingPrimaryTextColor,
                        togglePopover(setPopoverActiveThemeSettingPrimaryTextColor),
                        colorPickerFunc,
                        colorPicker,
                      )}
                      {colorField(
                        "Primary color",
                        "themeSettingPrimaryColor",
                        customizedata?.themeSettingPrimaryColor,
                        handleChangeValue,
                        popoverActiveThemeSettingPrimaryColor,
                        togglePopover(setPopoverActiveThemeSettingPrimaryColor),
                        colorPickerFunc,
                        colorPicker,
                      )}
                      {colorField(
                        "Background color",
                        "themeSettingBackgroundColor",
                        customizedata?.themeSettingBackgroundColor,
                        handleChangeValue,
                        popoverActiveThemeSettingBackgroundColor,
                        togglePopover(setPopoverActiveThemeSettingBackgroundColor),
                        colorPickerFunc,
                        colorPicker,
                      )}
                    </BlockStack>
                  </div>
                </div>
              ) : setting == "Email Subject" ? (
                <div className="h-full">
                  <Box padding={"400"}>
                    <InlineStack blockAlign="center" align="start" gap={"300"}>
                      <Button onClick={() => setSetting("Section")} size="medium" variant="tertiary" textAlign="center" icon={ChevronLeftIcon} />
                      <Text as="h1" variant="headingMd">
                        {setting}
                      </Text>
                    </InlineStack>
                  </Box>
                  <Divider />
                  <div className="flex flex-col gap-4 p-4 pb-16 overflow-y-auto scroll_custom" style={{ maxHeight: "calc(-116px + 100vh)" }}>
                    <div ref={emailSubjectDivRef}>
                      <TextField
                        label="Email Subject"
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
                                  sections={[
                                    {
                                      title: "Shipment",
                                      items: [
                                        { content: "Shipment status", onAction: () => insertVariable("{{shipment_status}}") },
                                        { content: "Tracking link", onAction: () => insertVariable("{{tracking_link}}") },
                                        { content: "Estimated delivery date", onAction: () => insertVariable("{{estimated_delivery_date}}") },
                                        { content: "Tracking number", onAction: () => insertVariable("{{tracking_number}}") },
                                        { content: "Carrier name", onAction: () => insertVariable("{{carrier_name}}") },
                                        { content: "Carrier contact", onAction: () => insertVariable("{{carrier_contact}}") },
                                        { content: "Last check point", onAction: () => insertVariable("{{last_check_point}}") },
                                        { content: "Last checkpoint time", onAction: () => insertVariable("{{last_checkpoint_time}}") },
                                        { content: "Transit time", onAction: () => insertVariable("{{transit_time}}") },
                                        { content: "Residence time", onAction: () => insertVariable("{{residence_time}}") },
                                        { content: "Order number", onAction: () => insertVariable("{{order_number}}") },
                                        { content: "Order date", onAction: () => insertVariable("{{order_date}}") },
                                        { content: "Fulfillment date", onAction: () => insertVariable("{{fulfillment_date}}") },
                                        { content: "Product name", onAction: () => insertVariable("{{product_name}}") },
                                      ],
                                    },
                                    {
                                      title: "Customer",
                                      items: [
                                        { content: "Customer email", onAction: () => insertVariable("{{customer_email}}") },
                                        { content: "Customer phone", onAction: () => insertVariable("{{customer_phone}}") },
                                        { content: "First name", onAction: () => insertVariable("{{first_name}}") },
                                        { content: "Last name", onAction: () => insertVariable("{{last_name}}") },
                                        { content: "Shipping country", onAction: () => insertVariable("{{shipping_country}}") },
                                        { content: "Shipping province", onAction: () => insertVariable("{{shipping_province}}") },
                                        { content: "Shipping city", onAction: () => insertVariable("{{shipping_city}}") },
                                        { content: "Shipping address1", onAction: () => insertVariable("{{shipping_address1}}") },
                                        { content: "Shipping address2", onAction: () => insertVariable("{{shipping_address2}}") },
                                        { content: "Shipping zip", onAction: () => insertVariable("{{shipping_zip}}") },
                                      ],
                                    },
                                  ]}
                                />
                              </Popover.Pane>
                            </Popover>
                          ),
                        }}
                        value={customizedata?.emailSubject}
                        onChange={(value) => handleChangeValue("emailSubject", value)}
                        multiline={4}
                        maxLength={200}
                        showCharacterCount
                        autoComplete="off"
                      />
                    </div>
                  </div>
                </div>
              ) : setting == "Branding" ? (
                <BrandingCustomizer
                  setting={setting}
                  handleSetting={handleSetting}
                  customizedata={customizedata}
                  handleChangeValue={handleChangeValue}
                  colorPicker={colorPicker}
                  colorPickerFunc={colorPickerFunc}
                  handleDropZoneDrop={handleDropZoneDrop}
                  handleRemoveLogo={handleRemoveLogo}
                />
              ) : setting == "Status description" ? (
                <div className="h-full Status_description">
                  <Box padding={"400"}>
                    <InlineStack blockAlign="center" align="start" gap={"300"}>
                      <Button onClick={() => handleSetting("Section")} size="medium" variant="tertiary" textAlign="center" icon={ChevronLeftIcon} />
                      <Text as="h1" variant="headingMd">
                        {setting}
                      </Text>
                    </InlineStack>
                  </Box>
                  <Divider />
                  <div className="flex flex-col gap-4 p-4 pb-16 overflow-y-auto scroll_custom" style={{ maxHeight: "calc(-116px + 100vh)" }}>
                    <BlockStack gap={"300"}>
                      <BlockStack gap={"100"}>
                        <Label>Content</Label>
                        <CKEditor
                          editor={ClassicEditor}
                          config={editorConfig}
                          data={customizedata?.statusDescriptionDetails}
                          onChange={(event, editor) => {
                            const data = editor.getData();
                            setCustomizeData((prevState) => ({
                              ...prevState,
                              statusDescriptionDetails: data,
                            }));
                          }}
                        />
                      </BlockStack>
                      {colorField(
                        "Background Color",
                        "statusDescriptionBackgroundColor",
                        customizedata?.statusDescriptionBackgroundColor,
                        handleChangeValue,
                        popoverActiveStatusDescriptionBackgroundColor,
                        togglePopover(setPopoverActiveStatusDescriptionBackgroundColor),
                        colorPickerFunc,
                        colorPicker,
                      )}
                      {colorField(
                        "Item Text Color",
                        "statusDescriptionTextColor",
                        customizedata?.statusDescriptionTextColor,
                        handleChangeValue,
                        popoverActiveStatusDescriptionTextColor,
                        togglePopover(setPopoverActiveStatusDescriptionTextColor),
                        colorPickerFunc,
                        colorPicker,
                      )}
                    </BlockStack>
                  </div>
                </div>
              ) : setting == "Tracking button" ? (
                <TrackingButton
                  setting={setting}
                  handleSetting={handleSetting}
                  customizedata={customizedata}
                  handleChangeValue={handleChangeValue}
                  colorPicker={colorPicker}
                  colorPickerFunc={colorPickerFunc}
                />
              ) : setting == "Additional details" ? (
                <AdditionalDetails
                  setting={setting}
                  handleSetting={handleSetting}
                  customizedata={customizedata}
                  handleChangeValue={handleChangeValue}
                  colorPicker={colorPicker}
                  colorPickerFunc={colorPickerFunc}
                  editorConfig={editorConfig}
                />
              ) : setting == "Line items" ? (
                <LineItemsCustomizer
                  setting={setting}
                  handleSetting={handleSetting}
                  customizedata={customizedata}
                  handleChangeValue={handleChangeValue}
                  colorPicker={colorPicker}
                  colorPickerFunc={colorPickerFunc}
                />
              ) : setting == "Featured products" ? (
                <FeaturedProductsCustomizer
                  setting={setting}
                  handleSetting={handleSetting}
                  customizedata={customizedata}
                  handleChangeValue={handleChangeValue}
                  colorPicker={colorPicker}
                  colorPickerFunc={colorPickerFunc}
                  selectedProducts={selectedProducts}
                  handleOpenProductModal={handleOpenProductModal}
                  handleRemoveProduct={handleRemoveProduct}
                />
              ) : setting == "Footer" ? (
                <FooterCustomizer
                  setting={setting}
                  handleSetting={handleSetting}
                  customizedata={customizedata}
                  handleChangeValue={handleChangeValue}
                  colorPicker={colorPicker}
                  colorPickerFunc={colorPickerFunc}
                />
              ) : (
                ""
              )}
            </div>

            <div
              className="bg-white overflow-y-scroll email_customizer"
              style={{
                width: "100%",
                background: "rgb(246, 246, 247)",
                height: "calc(-58px + 100vh)",
              }}
            >
              <div className="flex items-stretch justify-center">
                <div
                  className="w-full overflow-hidden"
                  style={{
                    maxWidth: view === "Desktop" ? "650px" : "360px",
                  }}
                >
                  <div className="h-full py-4">
                    <div className="h-full shadow-border scroll_custom">
                      <div
                        style={{
                          backgroundColor:
                            customizedata?.themeSettingBackgroundColor !== "" ? `#${customizedata?.themeSettingBackgroundColor}` : "white",
                          color: `#${customizedata?.themeSettingPrimaryTextColor}`,
                          textDecoration: "none",
                          fontFamily: customizedata?.themeSettingFontFamily,
                        }}
                      >
                        {loading ? (
                          <Box padding={"400"}>
                            <BlockStack gap={"400"}>
                              <SkeletonBodyText />
                              <SkeletonBodyText />
                              <SkeletonBodyText />
                              <SkeletonBodyText />
                              <SkeletonBodyText />
                              <SkeletonBodyText />
                              <SkeletonBodyText />
                              <SkeletonBodyText />
                              <SkeletonBodyText lines={2} />
                            </BlockStack>
                          </Box>
                        ) : (
                          <>
                            {sidebarList?.find((item) => item?.title === "Branding")?.visible && (
                              <div
                                onClick={() => handleSetting("Branding")}
                                className="relative cursor-pointer"
                                style={{
                                  color: `#${customizedata?.themeSettingPrimaryTextColor}`,
                                }}
                              >
                                <div className="section-content section-border">
                                  <div style={{ minHeight: "20px", display: "block" }}>
                                    <div
                                      style={{
                                        paddingTop: `${customizedata?.paddingTop}px`,
                                        paddingBottom: `${customizedata?.paddingBottom}px`,
                                        fontFamily: customizedata?.themeSettingFontFamily,
                                        backgroundColor: `#${customizedata?.brandingBackgroundColor}`,
                                        color: `#${customizedata?.textColor}`,
                                        display: customizedata?.brandingType?.includes("Store name") ? "block" : "none",
                                      }}
                                    >
                                      <h2 style={{ textAlign: "center", fontSize: "1.5em" }}>
                                        <strong>{`{store_name}`}</strong>
                                      </h2>
                                    </div>
                                    <div
                                      style={{
                                        paddingTop: `${customizedata?.paddingTop}px`,
                                        paddingBottom: `${customizedata?.paddingBottom}px`,
                                        backgroundColor: `#${customizedata?.brandingBackgroundColor}`,
                                        display: customizedata?.brandingType?.includes("Store name") ? "none" : "block",
                                      }}
                                    >
                                      {customizedata?.logo === "" || customizedata?.logo === null ? (
                                        <img
                                          src={NoneBackground}
                                          alt="brand"
                                          style={{ width: `${customizedata?.imageWidth}%`, margin: "0px auto" }}
                                        />
                                      ) : (
                                        <img
                                          src={window.URL.createObjectURL(customizedata?.logo)}
                                          alt="brand"
                                          style={{ width: `${customizedata?.imageWidth}%`, margin: "0px auto" }}
                                        />
                                      )}
                                    </div>
                                  </div>
                                </div>
                              </div>
                            )}
                            {sidebarList?.find((item) => item?.title === "Status description")?.visible && (
                              <div
                                onClick={() => handleSetting("Status description")}
                                className="relative cursor-pointer"
                                style={{
                                  color: `#${customizedata?.themeSettingPrimaryTextColor}`,
                                }}
                              >
                                <div className="section-content section-border">
                                  <div className="block">
                                    <div
                                      style={{
                                        color: `#${customizedata?.statusDescriptionTextColor}`,
                                        fontFamily: customizedata?.themeSettingFontFamily,
                                        backgroundColor: `#${customizedata?.statusDescriptionBackgroundColor}`,
                                      }}
                                    >
                                      <div
                                        className="py-[11.7px] px-[7.8px]"
                                        dangerouslySetInnerHTML={{ __html: customizedata?.statusDescriptionDetails }}
                                      />
                                    </div>
                                  </div>
                                </div>
                              </div>
                            )}
                            {sidebarList?.find((item) => item?.title === "Tracking button")?.visible && (
                              <div
                                onClick={() => handleSetting("Tracking button")}
                                className="relative cursor-pointer"
                                style={{
                                  color: `#${customizedata?.themeSettingPrimaryTextColor}`,
                                }}
                              >
                                <div className="section-content">
                                  <div
                                    className="px-8 py-4"
                                    style={{
                                      textAlign: customizedata?.trackingButtonAlignment,
                                      display: "block",
                                      backgroundColor: `#${customizedata?.trackingButtonBackgroundColor}`,
                                    }}
                                  >
                                    <button
                                      className="px-10 py-4 rounded-md"
                                      style={{
                                        backgroundColor: `#${customizedata?.trackingButtonButtonColor}`,
                                        color: `#${customizedata?.trackingButtonTextColor}`,
                                        fontFamily: customizedata?.themeSettingFontFamily,
                                        fontSize: `${customizedata?.trackingButtonFontSize}px`,
                                        width: `${customizedata?.trackingButtonButtonWidth}%`,
                                      }}
                                    >
                                      {customizedata?.trackingButtonButtonText}
                                    </button>
                                  </div>
                                </div>
                              </div>
                            )}
                            {sidebarList?.find((item) => item?.title === "Additional details")?.visible && (
                              <div
                                onClick={() => handleSetting("Additional details")}
                                className="relative cursor-pointer"
                                style={{
                                  color: `#${customizedata?.themeSettingPrimaryTextColor}`,
                                }}
                              >
                                <div className="section-content section-border">
                                  <div className="block">
                                    <div
                                      style={{
                                        color: `#${customizedata?.additionalDetailsTextColor}`,
                                        fontFamily: customizedata?.themeSettingFontFamily,
                                        backgroundColor: `#${customizedata?.additionalDetailsBackgroundColor}`,
                                      }}
                                    >
                                      <div
                                        className="py-[11.7px] px-[7.8px]"
                                        dangerouslySetInnerHTML={{ __html: customizedata?.additionalDetails }}
                                      />
                                    </div>
                                  </div>
                                </div>
                              </div>
                            )}
                            {sidebarList?.find((item) => item?.title === "Line items")?.visible && (
                              <div
                                onClick={() => handleSetting("Line items")}
                                className="relative cursor-pointer"
                                style={{
                                  color: `#${customizedata?.themeSettingPrimaryTextColor}`,
                                }}
                              >
                                <div className="section-content section-border">
                                  <div
                                    className="relative"
                                    style={{
                                      fontFamily: customizedata?.themeSettingFontFamily,
                                      backgroundColor: `#${customizedata?.lineItemBackgroundColor}`,
                                    }}
                                  >
                                    <Divider />
                                    <div className="px-5 py-2.5">
                                      <div
                                        className="flex flex-row justify-between"
                                        style={{
                                          color: `#${customizedata?.lineItemTitleColor}`,
                                          fontSize: `${customizedata?.lineItemTitleFontSize}px`,
                                        }}
                                      >
                                        <div className="font-bold">{customizedata?.lineItemMainTitle || "What's inside"}</div>
                                        {customizedata?.lineItemCurrencyAlignment === "Right" ? (
                                          <div className="font-bold">
                                            {customizedata?.lineItemProductAmountHide == 0
                                              ? `${customizedata?.lineItemAmount || "Amount"}(77.8 ${customizedata?.lineItemCurrency || "PKR"}) . ${
                                                  customizedata?.lineItemSubtitle || "Items"
                                                }(2)`
                                              : `${customizedata?.lineItemSubtitle || "Items"}(2)`}
                                          </div>
                                        ) : (
                                          <div className="font-bold">
                                            {customizedata?.lineItemProductAmountHide == 0
                                              ? `${customizedata?.lineItemAmount || "Amount"}(${customizedata?.lineItemCurrency || "PKR"} 77.8) . ${
                                                  customizedata?.lineItemSubtitle || "Items"
                                                }(2)`
                                              : `${customizedata?.lineItemSubtitle || "Items"}(2)`}
                                          </div>
                                        )}
                                      </div>
                                      <div
                                        className="grid grid-cols-1 pt-5 pb-2.5"
                                        style={{
                                          fontSize: `${customizedata?.lineItemProductFontSize}px`,
                                        }}
                                      >
                                        <div className="flex flex-row pb-4">
                                          <img src="https://fe.trackingmore.net/images/bag.png" alt="" width="60" height="60" />
                                          <div className="flex flex-col pl-4 text-left">
                                            <div
                                              style={{
                                                color: `#${customizedata?.lineItemItemTextColor}`,
                                              }}
                                            >
                                              Product title
                                            </div>
                                            {customizedata?.lineItemProductAmountHide == "0" ? (
                                              <div
                                                style={{
                                                  color: `#${customizedata?.lineItemItemTextColor}`,
                                                }}
                                              >
                                                {customizedata?.lineItemCurrencyAlignment === "Right"
                                                  ? `38.9 ${customizedata?.lineItemCurrency || "PKR"}`
                                                  : `${customizedata?.lineItemCurrency || "PKR"} 77.8`}
                                              </div>
                                            ) : (
                                              <div
                                                style={{
                                                  color: `#${customizedata?.lineItemItemTextColor}`,
                                                }}
                                              >
                                                SKU
                                              </div>
                                            )}
                                            {customizedata?.lineItemProductAmountHide == "0" ? (
                                              <div
                                                style={{
                                                  color: `#${customizedata?.lineItemItemTextColor}`,
                                                }}
                                              >
                                                {customizedata?.lineItemCurrencyAlignment === "Right"
                                                  ? `-5.00 ${customizedata?.lineItemCurrency || "PKR"}`
                                                  : `-${customizedata?.lineItemCurrency || "PKR"} 77.8`}
                                              </div>
                                            ) : (
                                              <div
                                                style={{
                                                  color: `#${customizedata?.lineItemItemTextColor}`,
                                                }}
                                              >
                                                x 1
                                              </div>
                                            )}
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            )}
                            {sidebarList?.find((item) => item?.title === "Featured products")?.visible && (
                              <div
                                onClick={() => handleSetting("Featured products")}
                                className="relative cursor-pointer"
                                style={{
                                  color: `#${customizedata?.themeSettingPrimaryTextColor}`,
                                }}
                              >
                                <div className="section-content">
                                  <div
                                    style={{
                                      display: "block",
                                    }}
                                  >
                                    <div
                                      className="text-center pt-2.5"
                                      style={{
                                        color: `#${customizedata?.featuredProductsTitleColor}`,
                                        fontFamily: customizedata?.themeSettingFontFamily,
                                        backgroundColor: `#${customizedata?.featuredProductsBackgroundColor}`,
                                      }}
                                    >
                                      {customizedata?.featuredProductsMainTitle || "Your may also like..."}
                                    </div>
                                    <div
                                      className="grid p-2.5 relative grid-cols-2"
                                      style={{
                                        color: `#${customizedata?.featuredProductsItemTextColor}`,
                                        fontFamily: customizedata?.themeSettingFontFamily,
                                        backgroundColor: `#${customizedata?.featuredProductsBackgroundColor}`,
                                        minHeight: "225px",
                                      }}
                                    >
                                      {console.log("selectedProducts", selectedProducts)}
                                      {selectedProducts?.map((product, index) => (
                                        <div key={index} className="cursor-pointer text-sm pt-2.5">
                                          <img className="px-2.5 w-[200px] m-auto object-contain" src={product?.image} alt="" />
                                          <p className="px-2.5 my-2.5 text-center">{product?.title}</p>
                                          {/* <div className="text-center py-2.5">11</div> */}
                                        </div>
                                      ))}
                                    </div>
                                  </div>
                                </div>
                              </div>
                            )}
                            {sidebarList?.find((item) => item?.title === "Footer")?.visible && (
                              <div
                                onClick={() => handleSetting("Footer")}
                                className="relative cursor-pointer"
                                style={{
                                  color: `#${customizedata?.themeSettingPrimaryTextColor}`,
                                }}
                              >
                                <div className="section-content">
                                  <Divider />
                                  <div
                                    className="flex-col items-center p-5"
                                    style={{
                                      color: `#${customizedata?.footerTextColor}`,
                                      backgroundColor: `#${customizedata?.footerBackgroundColor}`,
                                      fontFamily: customizedata?.themeSettingFontFamily,
                                      display: "flex",
                                      lineHeight: "20px",
                                    }}
                                  >
                                    <div className="w-full">
                                      <CKEditor
                                        editor={InlineEditor}
                                        config={editorConfig}
                                        data={customizedata?.footerDetails}
                                        onChange={(event, editor) => {
                                          const data = editor.getData();
                                          setCustomizeData((prevState) => ({
                                            ...prevState,
                                            footerDetails: data,
                                          }));
                                        }}
                                      />
                                    </div>
                                    {customizedata?.footerUnsubscribeButton == "0" && (
                                      <button className="Polaris-Button Polaris-Button--variantPlain" style={{}}>
                                        {customizedata?.footerUnsubscribe}
                                      </button>
                                    )}
                                  </div>
                                </div>
                              </div>
                            )}
                          </>
                        )}
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
      </div>
    </>
  );
}

const colorField = (label, key, value, handleChangeValue, popoverActiveTextColor, togglePopoverActiveTextColor, colorPickerFunc, colorPicker) => {
  return (
    <TextField
      label={label}
      value={value || ""}
      onChange={(value) => handleChangeValue(key, value)}
      autoComplete="off"
      prefix="#"
      connectedLeft={
        <Popover
          active={popoverActiveTextColor}
          activator={
            <button
              onClick={togglePopoverActiveTextColor}
              type="button"
              tabIndex="0"
              aria-controls=":ref:"
              aria-owns=":ref:"
              aria-expanded="false"
              data-state="closed"
              style={{
                borderRadius: "3px",
                border: "0px",
                padding: "0px",
                margin: "0px",
                display: "flex",
                cursor: "pointer",
                outline: "none",
              }}
            >
              <div
                style={{
                  borderRadius: "3px",
                  height: "2rem",
                  width: "2rem",
                  background:
                    "repeating-conic-gradient(rgb(255, 255, 255) 0deg, rgb(255, 255, 255) 25%, rgb(246, 246, 247) 0deg, rgb(246, 246, 247) 50%) 50% center / 0.5rem 0.5rem",
                  boxShadow: "rgba(0, 0, 0, 0.19) 0px 0px 0px 1px inset",
                  flexShrink: 0,
                }}
              >
                <div
                  style={{
                    borderRadius: "inherit",
                    boxShadow: "inherit",
                    height: "100%",
                    width: "100%",
                    background: `#${value}`,
                  }}
                ></div>
              </div>
            </button>
          }
          autofocusTarget="first-node"
          onClose={togglePopoverActiveTextColor}
        >
          <ColorPicker onChange={(newColor) => colorPickerFunc(newColor, key)} color={colorPicker} />
        </Popover>
      }
    />
  );
};
