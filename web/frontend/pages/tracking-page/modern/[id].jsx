import {
  BlockStack,
  Box,
  Button,
  ButtonGroup,
  Card,
  Checkbox,
  ChoiceList,
  Divider,
  DropZone,
  FormLayout,
  Icon,
  InlineGrid,
  InlineStack,
  Layout,
  LegacyStack,
  Link,
  Modal,
  OptionList,
  Page,
  Popover,
  Scrollable,
  Select,
  SkeletonBodyText,
  SkeletonDisplayText,
  SkeletonPage,
  Spinner,
  Text,
  TextField,
  Thumbnail,
  Toast,
} from "@shopify/polaris";
import React, { useCallback, useContext, useEffect, useState } from "react";
import {
  EditIcon,
  DeleteIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  CollectionIcon,
  SearchIcon,
  ViewIcon,
  ProductIcon,
  XSmallIcon,
} from "@shopify/polaris-icons";
import { useLocation, useNavigate } from "react-router-dom";
import { AppContext } from "../../../components";
import { useAppBridge } from "@shopify/app-bridge-react";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import { FreeUpsellAutomaticProd } from "../../../components/FreeUpsellAutomaticProd";

const getFileSrc = (file) => {
  if (file && typeof file === "string" && file.startsWith("https")) {
    return file;
  } else if (file instanceof File) {
    return window.URL.createObjectURL(file);
  } else {
    return "https://via.placeholder.com/50";
  }
};

export default function Modern() {
  const navigate = useNavigate();
  const appBridge = useAppBridge();
  const location = useLocation();
  const pageId = location?.pathname?.split("/").pop();
  const { apiUrl, appUrl, shop, modernTrackingPageStyle, modernTrackingPageTitle } = useContext(AppContext);
  const [toggleLoadData, setToggleLoadData] = useState(true);
  const [toggleLoadCollection, setToggleLoadCollection] = useState(true);
  const [btnLoading, setBtnLoading] = useState(false);
  const [loading, setLoading] = useState(true);
  const [toggleData, setToggleData] = useState(true);
  const [trackingPageData, setTrackingPageData] = useState("");
  const [errorToast, setErrorToast] = useState(false);
  const [successToast, setSuccessToast] = useState(false);
  const [toastMsg, setToastMsg] = useState("");
  const [data, setData] = useState({
    defaultTrackingURL: "https://trackify-app-testing-v16.myshopify.com/a/",
    extensionLabel: "track",
    search: "",
    store_name: "",
    store_information: "",
    phone_icon: "",
    second_link_type: "icon",
    second_link_icon: "",
    go_to_store_link: "",
    enable_tracking: 0,
    google_tag_manager: "",
    google_analytics_measurement_id: "",
    google_universal_analytics_tracking_id: "",
    google_remarketing_code: "",
    facebook_pixel: "",
    enable_related_links: 0,
    related_links: [],
    enable_FAQ_section: 0,
    section_type: ["Link"],
    link_to_another_page: "",
    questions: [],
    map_pin: ["No location"],
    color_styles: [modernTrackingPageStyle],
    custom_css: "",
  });

  console.log("data11", data);

  const [errors, setErrors] = useState({
    go_to_store_link: "",
    second_link_icon: "",
    // other error states if needed
  });
  const [file, setFile] = useState();
  const [secondIcon, setSecondIcon] = useState();
  const [activeLinkModal, setActiveLinkModal] = useState(false);
  const [typeOfLink, setTypeOfLink] = useState(["Icon"]);
  const [icon, setIcon] = useState("Facebook");
  const [label, setLabel] = useState("");
  const [link, setLink] = useState("");
  const [isEditingLink, setIsEditingLink] = useState(false);
  const [currentLinkIndex, setCurrentLinkIndex] = useState(null);
  const [activeQuestionModal, setActiveQuestionModal] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState(null);
  const [question, setQuestion] = useState("");
  const [answer, setAnswer] = useState("");
  const [productsList, setProductsList] = useState([]);
  const [modalProductsList, setModalProductsList] = useState([]);
  const [collectionsList, setCollectionsList] = useState([]);
  const [popoverActiveProducts, setPopoverActiveProducts] = useState(false);
  const [selectedProductsType, setSelectedProductsType] = useState(["automatic_products"]);
  const [hiddenProductModal, setHiddenProductModal] = useState(false);
  const [selectedHiddenProductsIDs, setSelectedHiddenProductsIDs] = useState([]);
  const [selectedHiddenProducts, setSelectedHiddenProducts] = useState([]);
  const [collectionModal, setCollectionModal] = useState(false);
  const [selectedCollectionsIDs, setSelectedCollectionsIDs] = useState(null);
  const [selectedCollections, setSelectedCollections] = useState(null);
  const [manualProductModal, setManualProductModal] = useState(false);
  const [selectedManualProductsIDs, setSelectedManualProductsIDs] = useState([]);
  const [selectedManualProducts, setSelectedManualProducts] = useState([]);
  const [productsData, setProductsData] = useState({
    product_source_for_recommendation: "first_line_item",
    product_recommendation_mode: "RELATED",
  });
  const [textFieldValueSearch, setTextFieldValueSearch] = useState("");
  const [productsLoading, setProductsLoading] = useState(true);

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
        setToggleLoadData(false);
        setProductsLoading(false);
      }
    } catch (error) {
      setProductsLoading(false);
    } finally {
      setProductsLoading(false);
    }
  };
  const fetchCollections = async () => {
    let sessionToken = await getSessionToken(appBridge);
    setProductsLoading(true);
    try {
      const response = await axios.get(`${apiUrl}get-all-collections`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      if (response?.status == 200) {
        setCollectionsList(response?.data?.all_collections);
        setProductsLoading(false);
      }
    } catch (error) {
      setProductsLoading(false);
    } finally {
      setProductsLoading(false);
    }
  };

  useEffect(() => {
    if (toggleLoadData) {
      fetchProductList();
    }
  }, [toggleLoadData, textFieldValueSearch]);

  useEffect(() => {
    if (toggleLoadCollection) {
      fetchCollections();
    }
  }, [toggleLoadCollection, textFieldValueSearch]);

  const handleTextFieldSearchChange = useCallback((value) => {
    setTextFieldValueSearch(value);
    setToggleLoadData(true);
  }, []);

  const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
  const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);
  const togglePopoverActiveProducts = useCallback(() => setPopoverActiveProducts((popoverActiveProducts) => !popoverActiveProducts), []);

  const handleChangesOpenHiddenProductModal = useCallback(() => {
    setHiddenProductModal(!hiddenProductModal);
    // setToggleLoadData(true);
  }, [hiddenProductModal]);

  const handleChangesCancelHiddenProductModal = useCallback(() => {
    setHiddenProductModal(!hiddenProductModal);
  }, [hiddenProductModal]);

  const handleChangesClearHiddenProduct = useCallback(() => {
    setSelectedHiddenProductsIDs([]);
    setSelectedHiddenProducts([]);
  }, []);

  const handleHiddenProductSelect = (id) => {
    // If the selected product is already in the list, remove it
    if (selectedHiddenProductsIDs?.includes(id)) {
      const newArray = selectedHiddenProductsIDs.filter((item) => item !== id);
      setSelectedHiddenProductsIDs(newArray);
    } else {
      // Add the selected product if the limit is not reached
      setSelectedHiddenProductsIDs([...selectedHiddenProductsIDs, id]);
    }
  };

  const handleHiddenProductsSaveModal = () => {
    setHiddenProductModal(false);
    const selectedProd = productsList?.filter((product) => selectedHiddenProductsIDs?.includes(product.shopify_product_id));
    setSelectedHiddenProducts(selectedProd);
  };

  const handleChangesOpenCollectionModal = useCallback(() => {
    setCollectionModal(!collectionModal);
    setToggleLoadCollection(true);
  }, [collectionModal]);

  const handleChangesCancelCollectionModal = useCallback(() => {
    setCollectionModal(!collectionModal);
  }, [collectionModal]);

  const handleCollectionSelect = (collectionId) => {
    setSelectedCollectionsIDs((prevCheckedCollection) => (prevCheckedCollection === collectionId ? null : collectionId));
  };

  const handleCollectionSaveModal = () => {
    setCollectionModal(false);
    const selectedColl = collectionsList?.find((collection) => collection?.shopify_collection_id === selectedCollectionsIDs);
    setSelectedCollections(selectedColl);
  };

  const handleRemoveCollection = () => {
    setSelectedCollectionsIDs(null);
    setSelectedCollections(null);
  };

  const handleChangesOpenManualProductModal = useCallback(() => {
    setManualProductModal(!manualProductModal);
    setToggleLoadData(true);
  }, [manualProductModal]);

  const handleChangesCancelManualProductModal = useCallback(() => {
    setManualProductModal(!manualProductModal);
  }, [manualProductModal]);

  const handleManualProductSelect = (id) => {
    // If the selected product is already in the list, remove it
    if (selectedManualProductsIDs?.includes(id)) {
      const newArray = selectedManualProductsIDs.filter((item) => item !== id);
      setSelectedManualProductsIDs(newArray);
    } else {
      // Add the selected product if the limit is not reached
      setSelectedManualProductsIDs([...selectedManualProductsIDs, id]);
    }
  };

  const handleRemoveManualProducts = (productId) => {
    const newArray = selectedManualProducts.filter((product) => product.shopify_product_id !== productId);
    const newIdsArray = selectedManualProductsIDs.filter((id) => id !== productId);
    setSelectedManualProductsIDs(newIdsArray);
    setSelectedManualProducts(newArray);
  };

  const handleManualProductsSaveModal = () => {
    setManualProductModal(false);
    const selectedProd = productsList?.filter((product) => selectedManualProductsIDs.includes(product.shopify_product_id));
    setSelectedManualProducts(selectedProd);
  };

  const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

  const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}tracking-page-detail/${pageId}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { tracking_page_data } = response?.data;
      const data = JSON.parse(tracking_page_data?.data);
      console.error("datadatadatadatadata:", data);
      setTrackingPageData(tracking_page_data);
      setData(data?.pageData);
      setFile(tracking_page_data?.logo ? `${appUrl}${tracking_page_data?.logo}` : "" || "");
      setSecondIcon(tracking_page_data?.icon ? `${appUrl}${tracking_page_data?.icon}` : "" || "");
      setProductsData((prevState) => ({
        ...prevState,
        product_source_for_recommendation: data?.product_source_for_recommendation,
        product_recommendation_mode: data?.product_recommendation_mode,
      }));
      setSelectedProductsType([data?.selectedProductsType]);
      setSelectedHiddenProductsIDs(data?.hidden_products?.map((product) => product?.shopify_product_id) || []);

      setSelectedHiddenProducts(data?.hidden_products || []);
      setSelectedCollectionsIDs(data?.selected_collection?.selected_collection_id);

      setSelectedCollections(data?.selected_collection || null);

      setSelectedManualProductsIDs(data?.selected_manual_product?.map((product) => product?.shopify_product_id) || []);

      setSelectedManualProducts(data?.selected_manual_product || []);
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

  const handleChangeData = (fieldName, value) => {
    setProductsData((prevFieldObject) => ({
      ...prevFieldObject,
      [fieldName]: value,
    }));
  };

  const handleDropZoneDrop = useCallback((_dropFiles, acceptedFiles, _rejectedFiles) => setFile(acceptedFiles[0]), []);
  const handleDropZoneDropSecondIcon = useCallback((_dropFiles, acceptedFiles, _rejectedFiles) => setSecondIcon(acceptedFiles[0]), []);
  const handleChangeQuestionModal = useCallback(() => setActiveQuestionModal(!activeQuestionModal), [activeQuestionModal]);
  const handleChangeLinkModal = useCallback(() => setActiveLinkModal(!activeLinkModal), [activeLinkModal]);

  const validImageTypes = ["image/gif", "image/jpeg", "image/png"];

  const fileUpload = !file && <DropZone.FileUpload actionTitle="Add Image" />;
  const fileUploadSecondIcon = !secondIcon && <DropZone.FileUpload actionTitle="Add Icon" />;

  function convertNumberToBoolean(value) {
    let booleanValue;
    if (value == 1) {
      booleanValue = true;
    } else {
      booleanValue = false;
    }
    return booleanValue;
  }

  const validateURL = (url) => {
    // Basic URL validation using URL constructor
    try {
      new URL(url);
      return true;
    } catch (e) {
      return false;
    }
  };

  const handleChangeValue = useCallback((field, value) => {
    if (field === "go_to_store_link") {
      // Validate URL if field is go_to_store_link
      const isValidURL = validateURL(value);
      if (!isValidURL && value.trim() !== "") {
        setErrors((prevErrors) => ({
          ...prevErrors,
          [field]: "Please enter a valid URL.",
        }));
      } else {
        setErrors((prevErrors) => ({
          ...prevErrors,
          [field]: "",
        }));
      }
    }
    if (field === "second_link_icon") {
      // Validate URL if field is go_to_store_link
      const isValidURL = validateURL(value);
      if (!isValidURL && value.trim() !== "") {
        setErrors((prevErrors) => ({
          ...prevErrors,
          [field]: "Please enter a valid URL.",
        }));
      } else {
        setErrors((prevErrors) => ({
          ...prevErrors,
          [field]: "",
        }));
      }
    }

    setData((prevState) => ({
      ...prevState,
      [field]: value,
    }));
  }, []);

  const handleChangeCheckbox = (fieldName, value) => {
    let enableValue = "";

    if (value == 0) {
      enableValue = 1;
    } else {
      enableValue = 0;
    }

    setData((prevFieldObject) => ({
      ...prevFieldObject,
      [fieldName]: enableValue,
    }));
  };

  const handleAddOrEditQuestion = () => {
    if (isEditing && currentQuestionIndex !== null) {
      // Update existing question
      const updatedQuestions = [...data.questions];
      updatedQuestions[currentQuestionIndex] = { ...updatedQuestions[currentQuestionIndex], question, answer };
      setData((prevData) => ({ ...prevData, questions: updatedQuestions }));
    } else {
      // Add new question
      const newQuestion = {
        id: Date.now(), // Use current timestamp as ID
        question,
        answer,
      };
      setData((prevData) => ({
        ...prevData,
        questions: [...(prevData?.questions ?? []), newQuestion], // Fallback to an empty array if undefined or null
      }));
    }

    // Reset state
    setQuestion("");
    setAnswer("");
    setCurrentQuestionIndex(null);
    setIsEditing(false);
    handleChangeQuestionModal(); // Close the modal
  };

  const handleEdit = (index) => {
    setCurrentQuestionIndex(index);
    setQuestion(data.questions[index]?.question || "");
    setAnswer(data.questions[index]?.answer || "");
    setIsEditing(true);
    handleChangeQuestionModal(); // Open the modal
  };

  const handleDelete = (index) => {
    const updatedQuestions = data.questions.filter((_, i) => i !== index);
    setData((prevData) => ({ ...prevData, questions: updatedQuestions }));
  };

  const handleAddOrEditLink = () => {
    if (isEditingLink && currentLinkIndex !== null) {
      // Update existing question
      const updatedQuestions = [...data.related_links];
      updatedQuestions[currentLinkIndex] = { ...updatedQuestions[currentLinkIndex], question, answer };
      setData((prevData) => ({ ...prevData, related_links: updatedQuestions }));
    } else {
      // Add new question
      const newQuestion = {
        id: Date.now(), // Use current timestamp as ID
        typeOfLink,
        icon,
        label,
        link,
      };
      setData((prevData) => ({ ...prevData, related_links: [...prevData.related_links, newQuestion] }));
    }

    // Reset state
    setTypeOfLink(["Icon"]);
    setIcon("Facebook");
    setLabel("");
    setLink("");
    setCurrentLinkIndex(null);
    setIsEditingLink(false);
    handleChangeLinkModal(); // Close the modal
  };

  const handleEditLink = (index) => {
    setCurrentLinkIndex(index);
    setTypeOfLink(data.related_links[index]?.typeOfLink || "");
    setIcon(data.related_links[index]?.icon || "");
    setLabel(data.related_links[index]?.label || "");
    setLink(data.related_links[index]?.link || "");
    setIsEditingLink(true);
    handleChangeLinkModal(); // Open the modal
  };

  const handleDeleteLink = (index) => {
    const updatedQuestions = data.related_links.filter((_, i) => i !== index);
    setData((prevData) => ({ ...prevData, related_links: updatedQuestions }));
  };

  const handleAddPage = async (btnLoading) => {
    setBtnLoading((prev) => ({ [btnLoading]: !prev[btnLoading] }));

    try {
      const sessionToken = await getSessionToken(appBridge);

      const getFile = (file) => (validImageTypes.includes(file?.type) ? file : file || "");

      const payload = {
        theme_type: "Modern",
        page_name: trackingPageData?.page_name,
        logo: getFile(file),
        icon: getFile(secondIcon),
        data: {
          pageData: data,
          selectedProductsType: selectedProductsType[0],
          hidden_products: selectedHiddenProducts,
          product_source_for_recommendation: productsData?.product_source_for_recommendation,
          product_recommendation_mode: productsData?.product_recommendation_mode,
          selected_collection: selectedCollections,
          selected_manual_product: selectedManualProducts,
        },
      };

      const response = await axios.post(`${apiUrl}tracking-page-detail-save/${pageId}`, payload, {
        headers: {
          "Content-Type": "multipart/form-data",
          Authorization: `Bearer ${sessionToken}`,
        },
      });

      setSuccessToast(true);
      setToastMsg(response?.data?.message);
    } catch (error) {
      console.error("Error saving tracking page details:", error);
    } finally {
      setBtnLoading(false);
      setTimeout(() => {
        navigate("/tracking-page");
      }, 1500);
    }
  };

  return (
    <>
      <Modal
        open={activeLinkModal}
        onClose={handleChangeLinkModal}
        title="Header Links"
        primaryAction={{
          content: isEditingLink ? "Update" : "Add",
          onAction: handleAddOrEditLink,
        }}
        secondaryActions={[
          {
            content: "Cancel",
            onAction: handleChangeLinkModal,
          },
        ]}
      >
        <Scrollable style={{ height: "fit-content" }}>
          <Box padding={"400"}>
            <FormLayout>
              <ChoiceList
                title="Type of link"
                choices={[
                  { label: "Icon", value: "Icon" },
                  { label: "Text", value: "Text" },
                ]}
                selected={typeOfLink}
                onChange={(value) => setTypeOfLink(value)}
              />
              {typeOfLink == "Icon" ? (
                <Select
                  label="Date range"
                  options={[
                    { label: "Facebook", value: "Facebook" },
                    { label: "Instagram", value: "Instagram" },
                    { label: "Pinterest", value: "Pinterest" },
                    { label: "Snapchat", value: "Snapchat" },
                    { label: "TikTok", value: "TikTok" },
                    { label: "Twitter", value: "Twitter" },
                    { label: "Youtube", value: "Youtube" },
                  ]}
                  onChange={(value) => setIcon(value)}
                  value={icon}
                />
              ) : (
                <TextField label="Label" value={label} onChange={(value) => setLabel(value)} autoComplete="off" />
              )}
              <TextField label="Link" value={link} onChange={(value) => setLink(value)} autoComplete="off" />
            </FormLayout>
          </Box>
        </Scrollable>
      </Modal>

      <Modal
        open={activeQuestionModal}
        onClose={handleChangeQuestionModal}
        title="FAQ Item"
        primaryAction={{
          content: isEditing ? "Update" : "Add",
          onAction: handleAddOrEditQuestion,
        }}
        secondaryActions={[
          {
            content: "Cancel",
            onAction: handleChangeQuestionModal,
          },
        ]}
      >
        <Scrollable style={{ height: "220px" }}>
          <Box padding={"400"}>
            <FormLayout>
              <TextField
                label="Question"
                value={question}
                onChange={(value) => setQuestion(value)}
                autoComplete="off"
                placeholder="Enter question here"
              />
              <TextField
                label="Answer"
                value={answer}
                onChange={(value) => setAnswer(value)}
                autoComplete="email"
                multiline={4}
                placeholder="Enter answer here"
              />
            </FormLayout>
          </Box>
        </Scrollable>
      </Modal>

      <Modal
        open={hiddenProductModal}
        size="fullScreen"
        onClose={handleChangesCancelHiddenProductModal}
        title="Add products"
        primaryAction={{
          content: "Add",
          disabled: !selectedHiddenProductsIDs?.length,
          onAction: handleHiddenProductsSaveModal,
        }}
        secondaryActions={[
          {
            content: "Cancel",
            onAction: handleChangesCancelHiddenProductModal,
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
                const isSelectedId = selectedHiddenProductsIDs?.includes(product.shopify_product_id);
                return (
                  <div className="product-list-item" key={i}>
                    <Checkbox labelHidden checked={isSelectedId} onChange={() => handleHiddenProductSelect(product.shopify_product_id)} />
                    <div className="product-list-item-product-title" onClick={() => handleHiddenProductSelect(product.shopify_product_id)}>
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
      <Modal
        open={collectionModal}
        size="fullScreen"
        onClose={handleChangesCancelCollectionModal}
        title="Add collection"
        primaryAction={{
          content: "Add",
          disabled: selectedCollectionsIDs == null,
          onAction: handleCollectionSaveModal,
        }}
        secondaryActions={[
          {
            content: "Cancel",
            onAction: handleChangesCancelCollectionModal,
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
            ) : collectionsList?.length ? (
              collectionsList?.map((collection, i) => {
                const isSelectedId = selectedCollectionsIDs == collection?.shopify_collection_id;
                return (
                  <div
                    className="product-list-item"
                    style={{
                      backgroundColor: selectedCollectionsIDs !== null && !isSelectedId ? "var(--p-color-bg-surface-secondary)" : "unset",
                      color: selectedCollectionsIDs !== null && !isSelectedId ? "var(--p-color-text-disabled)" : "unset",
                    }}
                    key={i}
                  >
                    <Checkbox
                      labelHidden
                      checked={isSelectedId}
                      disabled={selectedCollectionsIDs !== null && !isSelectedId}
                      onChange={() => handleCollectionSelect(collection?.shopify_collection_id)}
                    />
                    <div className="product-list-item-product-title" onClick={() => handleCollectionSelect(collection?.shopify_collection_id)}>
                      <div className="product-list-item-product-title-inner">
                        <div className="product-list-item-product-title-thumbnail">
                          <Thumbnail source={collection?.image || ""} size="small" />
                        </div>
                        <div className="product-list-item-product-title-text">
                          <div className="ExJYf">
                            <div className="K2zxu">
                              <span>{collection?.title}</span>
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
                  No Collection Found
                </Text>
              </div>
            )}
          </Scrollable>
        </div>
      </Modal>
      <Modal
        open={manualProductModal}
        size="fullScreen"
        onClose={handleChangesCancelManualProductModal}
        title="Add products"
        primaryAction={{
          content: "Add",
          disabled: !selectedManualProductsIDs?.length,
          onAction: handleManualProductsSaveModal,
        }}
        secondaryActions={[
          {
            content: "Cancel",
            onAction: handleChangesCancelManualProductModal,
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
                const isSelectedId = selectedManualProductsIDs?.includes(product.shopify_product_id);
                return (
                  <div className="product-list-item" key={i}>
                    <Checkbox labelHidden checked={isSelectedId} onChange={() => handleManualProductSelect(product.shopify_product_id)} />
                    <div className="product-list-item-product-title" onClick={() => handleManualProductSelect(product.shopify_product_id)}>
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

      {loading ? (
        <SkeletonPage primaryAction>
          <Layout>
            <Layout.Section>
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack gap={"200"}>
                    <SkeletonDisplayText />
                    <SkeletonBodyText lines={1} />
                  </BlockStack>
                  <SkeletonDisplayText maxWidth="100%" />
                </BlockStack>
              </Card>
            </Layout.Section>
            <Layout.Section variant="fullWidth">
              <Card padding={0}>
                <Box paddingBlockStart={"400"} paddingInlineStart={"400"} paddingInlineEnd={"400"}>
                  <BlockStack gap={"200"}>
                    <SkeletonDisplayText />
                    <SkeletonBodyText lines={1} />
                  </BlockStack>
                </Box>
                <Box padding={"400"}>
                  <BlockStack gap={"400"}>
                    <BlockStack gap={"100"}>
                      <SkeletonDisplayText />
                      <SkeletonBodyText lines={2} />
                    </BlockStack>
                    <SkeletonBodyText lines={4} />
                  </BlockStack>
                </Box>
                <Divider />
                <Box padding={"400"}>
                  <BlockStack gap={"100"}>
                    <SkeletonDisplayText />
                    <SkeletonBodyText lines={2} />
                  </BlockStack>
                </Box>
                <Divider />
                <Box padding={"400"}>
                  <BlockStack gap={"100"}>
                    <SkeletonDisplayText />
                    <SkeletonBodyText lines={2} />
                  </BlockStack>
                </Box>
                <Divider />
                <Box padding={"400"}>
                  <BlockStack gap={"100"}>
                    <SkeletonDisplayText />
                    <SkeletonBodyText lines={2} />
                  </BlockStack>
                </Box>
              </Card>
            </Layout.Section>
          </Layout>
        </SkeletonPage>
      ) : (
        <Page
          title="Modern tracking page"
          backAction={{
            content: "Products",
            onAction: () => navigate("/tracking-page"),
          }}
          primaryAction={{
            content: "Save",
            loading: btnLoading["Save"],
            onAction: () => handleAddPage("Save"),
          }}
        >
          <Layout>
            {/* <Layout.Section variant="fullWidth">
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack>
                    <Text as="h2" variant="headingMd">
                      Tracking page URL
                    </Text>
                  </BlockStack>
                  <FormLayout>
                    <FormLayout.Group>
                      <Select
                        label="Default tracking url"
                        options={[
                          {
                            label: "https://trackify-app-testing-v16.myshopify.com/a/",
                            value: "https://trackify-app-testing-v16.myshopify.com/a/",
                          },
                          {
                            label: "https://trackify-app-testing-v16.myshopify.com/apps/",
                            value: "https://trackify-app-testing-v16.myshopify.com/apps/",
                          },
                          {
                            label: "https://trackify-app-testing-v16.myshopify.com/community/",
                            value: "https://trackify-app-testing-v16.myshopify.com/community/",
                          },
                          {
                            label: "https://trackify-app-testing-v16.myshopify.com/tools/",
                            value: "https://trackify-app-testing-v16.myshopify.com/tools/",
                          },
                        ]}
                        onChange={(value) => handleChangeValue("defaultTrackingURL", value)}
                        value={data?.defaultTrackingURL}
                      />
                      <TextField
                        label="Extension label"
                        value={data?.extensionLabel}
                        onChange={(value) => handleChangeValue("extensionLabel", value)}
                        autoComplete="off"
                      />
                    </FormLayout.Group>
                  </FormLayout>
                  <BlockStack gap={"100"}>
                    <InlineStack gap={"100"} blockAlign="center">
                      <Text>Permalink: </Text>
                      <Link>
                        {data?.defaultTrackingURL}
                        {data.extensionLabel}
                      </Link>
                    </InlineStack>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Once you make this page default, you can skip the `v` paramether.
                    </Text>
                  </BlockStack>
                </BlockStack>
              </Card>
            </Layout.Section> */}
            <Layout.Section variant="fullWidth">
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack>
                    <Text as="h2" variant="headingMd">
                      Search
                    </Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Select the options customers can search for when they navigate directly to the tracking page to find their shipment information.
                    </Text>
                  </BlockStack>
                  <Select
                    label="Search"
                    labelHidden
                    options={[
                      { label: "Any of email or order number", value: "any" },
                      { label: "Order number and email", value: "order_with_email" },
                      { label: "Order number", value: "order" },
                      { label: "Just email", value: "email" },
                    ]}
                    helpText="Additionally, customers will have the capability to search for package tracking numbers and shipping UUIDs."
                    onChange={(value) => handleChangeValue("search", value)}
                    value={data?.search}
                  />
                </BlockStack>
              </Card>
            </Layout.Section>
            <Layout.Section variant="fullWidth">
              <Card padding={0}>
                <Box paddingBlockStart={"400"} paddingInlineStart={"400"} paddingInlineEnd={"400"}>
                  <BlockStack>
                    <Text as="h2" variant="headingMd">
                      Brand information
                    </Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Providing additional information about your brand on the tracking page.
                    </Text>
                  </BlockStack>
                </Box>
                <Box padding={"400"}>
                  <BlockStack gap={"400"}>
                    <TextField
                      label="Store name"
                      value={data?.store_name}
                      onChange={(value) => handleChangeValue("store_name", value)}
                      placeholder="Enter your store or brand name"
                      helpText="Name of your store visible to your customers"
                      autoComplete="off"
                    />
                    {file ? (
                      <InlineStack align="space-between" blockAlign="center">
                        <Thumbnail size="large" alt={file.name} source={getFileSrc(file)} />
                        <Button variant="plain" size="medium" textAlign="center" tone="critical" onClick={() => setFile()}>
                          Remove
                        </Button>
                      </InlineStack>
                    ) : (
                      <DropZone allowMultiple={false} onDrop={handleDropZoneDrop}>
                        {/* {uploadedFile} */}
                        {fileUpload}
                      </DropZone>
                    )}
                  </BlockStack>
                </Box>
                <Divider />
                <Box padding={"400"}>
                  <TextField
                    label="Store information"
                    value={data?.store_information}
                    onChange={(value) => handleChangeValue("store_information", value)}
                    placeholder="Any information you want included"
                    multiline={4}
                    autoComplete="off"
                  />
                </Box>
                <Divider />
                <Box padding={"400"}>
                  <TextField
                    label="Phone icon"
                    value={data?.phone_icon}
                    onChange={(value) => handleChangeValue("phone_icon", value)}
                    placeholder="tel:+1 ..."
                    helpText="Can be tel:+1... or HTTP link if you want to use live support."
                    autoComplete="off"
                  />
                </Box>
                <Divider />
                <Box padding={"400"}>
                  <BlockStack gap={"400"}>
                    <ChoiceList
                      title="Second icon"
                      choices={[
                        { label: "Icon", value: "icon" },
                        { label: "Link", value: "link" },
                      ]}
                      selected={data?.second_link_type || ["icon"]}
                      onChange={(value) => handleChangeValue("second_link_type", value)}
                    />
                    {data?.second_link_type == "icon" &&
                      (secondIcon ? (
                        <InlineStack align="space-between" blockAlign="center">
                          <Thumbnail size="large" alt={secondIcon.name} source={getFileSrc(secondIcon)} />
                          <Button variant="plain" size="medium" textAlign="center" tone="critical" onClick={() => setSecondIcon()}>
                            Remove
                          </Button>
                        </InlineStack>
                      ) : (
                        <DropZone allowMultiple={false} onDrop={handleDropZoneDropSecondIcon}>
                          {fileUploadSecondIcon}
                        </DropZone>
                      ))}
                    {data?.second_link_type == "link" && (
                      <TextField
                        label="Second link icon"
                        value={data?.second_link_icon}
                        onChange={(value) => handleChangeValue("second_link_icon", value)}
                        placeholder="https://..."
                        autoComplete="off"
                        error={errors.second_link_icon}
                      />
                    )}
                  </BlockStack>
                </Box>
                <Divider />
                <Box padding={"400"}>
                  <TextField
                    label="Go to Store link"
                    value={data?.go_to_store_link}
                    onChange={(value) => handleChangeValue("go_to_store_link", value)}
                    placeholder="https://..."
                    helpText="Customers will land when they click on the store logo or Go To Shop button on the tracking page. We recommend sending customers to e.g. Homepage, Best-Selling collection, etc."
                    autoComplete="off"
                    error={errors.go_to_store_link}
                  />
                </Box>
              </Card>
            </Layout.Section>
            <Layout.Section variant="fullWidth">
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack>
                    <Text as="h2" variant="headingMd">
                      Tracking & Analytics
                    </Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Monitor page views, interactions, and events on the tracking page.
                    </Text>
                  </BlockStack>
                  <Checkbox
                    label="Enable Tracking"
                    checked={convertNumberToBoolean(data?.enable_tracking)}
                    onChange={() => handleChangeCheckbox("enable_tracking", data?.enable_tracking)}
                  />
                  {convertNumberToBoolean(data?.enable_tracking) && (
                    <>
                      <TextField
                        label="Google Tag Manager"
                        value={data?.google_tag_manager}
                        onChange={(value) => handleChangeValue("google_tag_manager", value)}
                        helpText="We strongly recommend using GTM for incorporating scripts and pixels explicitly on the tracking page, as it positively impacts page load times."
                        autoComplete="off"
                      />
                      <TextField
                        label="Google Analytics 4 (GA4), Measurement ID"
                        value={data?.google_analytics_measurement_id}
                        onChange={(value) => handleChangeValue("google_analytics_measurement_id", value)}
                        helpText="Send a page view event when it loads"
                        autoComplete="off"
                      />
                      <TextField
                        label="Google Universal Analytics - Tracking ID"
                        value={data?.google_universal_analytics_tracking_id}
                        onChange={(value) => handleChangeValue("google_universal_analytics_tracking_id", value)}
                        helpText="To track page views & events for your tracking page using Google Analytics, enter your tracking ID."
                        autoComplete="off"
                      />
                      <TextField
                        label="Google Remarketing Code"
                        value={data?.google_remarketing_code}
                        onChange={(value) => handleChangeValue("google_remarketing_code", value)}
                        helpText={`To add Google remarketing code to your app listing, enter the number that follows "var google_conversion_id = " in the remarketing tag you received from Google.`}
                        autoComplete="off"
                      />
                      <TextField
                        label="Facebook Pixel"
                        value={data?.facebook_pixel}
                        onChange={(value) => handleChangeValue("facebook_pixel", value)}
                        helpText="To add Facebook Pixel tracking to your app listing, enter the ID number for your app from the Facebook Event Manager."
                        autoComplete="off"
                      />
                    </>
                  )}
                </BlockStack>
              </Card>
            </Layout.Section>
            <Layout.Section variant="fullWidth">
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack>
                    <Text as="h2" variant="headingMd">
                      Related links
                    </Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Include links in the tracking page header that direct customers to your top-selling collections or other useful information.
                    </Text>
                  </BlockStack>
                  <Checkbox
                    label="Enable links"
                    checked={convertNumberToBoolean(data?.enable_related_links)}
                    onChange={() => handleChangeCheckbox("enable_related_links", data?.enable_related_links)}
                  />
                  {convertNumberToBoolean(data?.enable_related_links) && (
                    <BlockStack gap={"200"}>
                      <InlineStack align="space-between" blockAlign="center">
                        <Text as="h2" variant="headingSm">
                          Links
                        </Text>
                        <Button
                          onClick={() => {
                            setIsEditingLink(false);
                            handleChangeLinkModal();
                          }}
                          variant="plain"
                          size="medium"
                          textAlign="center"
                        >
                          Add link
                        </Button>
                      </InlineStack>
                      <Box borderColor="border" borderWidth="025" borderStyle="solid" borderRadius="200">
                        {!data?.related_links?.length ? (
                          <Box padding={"400"}>
                            <Text as="span" variant="bodyMd" tone="subdued">
                              You haven't created any header links yet.
                            </Text>
                          </Box>
                        ) : (
                          data?.related_links?.map((item, index) => (
                            <>
                              <Box padding={"400"} key={item.id}>
                                <InlineStack align="space-between" blockAlign="center">
                                  <BlockStack gap={"100"}>
                                    <Text as="span" variant="bodyMd">
                                      {item?.icon}
                                    </Text>
                                    <Text as="span" variant="bodyMd" tone="subdued">
                                      {item?.link}
                                    </Text>
                                  </BlockStack>
                                  <ButtonGroup>
                                    <Button size="medium" textAlign="center" icon={EditIcon} onClick={() => handleEditLink(index)}></Button>
                                    <Button size="medium" textAlign="center" icon={DeleteIcon} onClick={() => handleDeleteLink(index)}></Button>
                                  </ButtonGroup>
                                </InlineStack>
                              </Box>
                              {data?.related_links?.length - 1 !== index && <Divider />}
                            </>
                          ))
                        )}
                      </Box>
                    </BlockStack>
                  )}
                </BlockStack>
              </Card>
            </Layout.Section>
            <Layout.Section variant="fullWidth">
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack>
                    <Text as="h2" variant="headingMd">
                      FAQ section
                    </Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Incorporate an FAQ section on your tracking page to assist with answering the most common questions customers have regarding
                      their shipping and orders.
                    </Text>
                  </BlockStack>
                  <Checkbox
                    label="Enable FAQ section"
                    checked={convertNumberToBoolean(data?.enable_FAQ_section)}
                    onChange={() => handleChangeCheckbox("enable_FAQ_section", data?.enable_FAQ_section)}
                  />
                  {convertNumberToBoolean(data?.enable_FAQ_section) && (
                    <>
                      <ChoiceList
                        title="Section type"
                        choices={[
                          { label: "Link", value: "Link" },
                          { label: "Content", value: "Content" },
                        ]}
                        selected={data?.section_type}
                        onChange={(selected) => handleChangeValue("section_type", selected)}
                      />
                      {data?.section_type == "Link" ? (
                        <TextField
                          label="Link to another page"
                          value={data?.link_to_another_page}
                          onChange={(value) => handleChangeValue("link_to_another_page", value)}
                          autoComplete="off"
                        />
                      ) : (
                        <BlockStack gap={"200"}>
                          <InlineStack align="space-between" blockAlign="center">
                            <Text as="h2" variant="headingSm">
                              Questions
                            </Text>
                            <Button
                              onClick={() => {
                                setIsEditing(false);
                                handleChangeQuestionModal();
                              }}
                              variant="plain"
                              size="medium"
                              textAlign="center"
                            >
                              Add question
                            </Button>
                          </InlineStack>
                          <Box borderColor="border" borderWidth="025" borderStyle="solid" borderRadius="200">
                            {!data?.questions?.length ? (
                              <Box padding={"400"}>
                                <Text as="span" variant="bodyMd" tone="subdued">
                                  You haven't created any questions and answers yet.
                                </Text>
                              </Box>
                            ) : (
                              data?.questions?.map((item, index) => (
                                <>
                                  <Box padding={"400"} key={item.id}>
                                    <InlineStack align="space-between" blockAlign="center">
                                      <Text as="span" variant="bodyMd">
                                        {item?.question}
                                      </Text>
                                      <ButtonGroup>
                                        <Button size="medium" textAlign="center" icon={EditIcon} onClick={() => handleEdit(index)}></Button>
                                        <Button size="medium" textAlign="center" icon={DeleteIcon} onClick={() => handleDelete(index)}></Button>
                                      </ButtonGroup>
                                    </InlineStack>
                                  </Box>
                                  {data?.questions?.length - 1 !== index && <Divider />}
                                </>
                              ))
                            )}
                          </Box>
                        </BlockStack>
                      )}
                    </>
                  )}
                </BlockStack>
              </Card>
            </Layout.Section>
            <Layout.Section variant="fullWidth">
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack>
                    <Text as="h2" variant="headingMd">
                      Map pin
                    </Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Configure where you want the tracking pin to be positioned
                    </Text>
                  </BlockStack>
                  <ChoiceList
                    choices={[
                      { label: "No location", value: "No location" },
                      { label: "Order shipping address", value: "Order shipping address" },
                      { label: "Current carrier location", value: "Current carrier location" },
                    ]}
                    selected={data?.map_pin}
                    onChange={(selected) => handleChangeValue("map_pin", selected)}
                  />
                </BlockStack>
              </Card>
            </Layout.Section>
            <Layout.Section variant="fullWidth">
              <Card>
                <BlockStack gap={"400"}>
                  <Text as="h2" variant="headingMd">
                    Color styles
                  </Text>
                  <ChoiceList
                    choices={[
                      { label: "Dark", value: "Dark" },
                      { label: "Light", value: "Light" },
                    ]}
                    selected={data?.color_styles || [""]}
                    onChange={(selected) => handleChangeValue("color_styles", selected)}
                  />
                </BlockStack>
              </Card>
            </Layout.Section>
            <Layout.Section variant="fullWidth">
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack>
                    <Text as="h2" variant="headingMd">
                      Custom CSS
                    </Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Advanced branding for your tracking through custom CSS. Discover how to do it here
                    </Text>
                  </BlockStack>
                  <TextField
                    label="Custom CSS"
                    labelHidden
                    value={data?.custom_css}
                    onChange={(value) => handleChangeValue("custom_css", value)}
                    multiline={10}
                    autoComplete="off"
                  />
                </BlockStack>
              </Card>
            </Layout.Section>
            <Layout.Section variant="fullWidth">
              <Card>
                <BlockStack gap={"400"}>
                  <BlockStack>
                    <Text as="h2" variant="headingMd">
                      Products
                    </Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Select the product(s) that will be shown in checkout.
                    </Text>
                  </BlockStack>
                  <BlockStack>
                    <Box background="bg-fill" borderColor="border" borderStyle="solid" borderRadius="200" borderWidth="025">
                      <BlockStack>
                        <BlockStack>
                          <Box paddingBlockStart={"400"} paddingBlockEnd={"0"} paddingInlineStart={"400"} paddingInlineEnd={"400"}>
                            <BlockStack gap={"050"}>
                              <Popover
                                fullWidth
                                active={popoverActiveProducts}
                                activator={
                                  <ProductPopoverActivator
                                    togglePopoverActiveProducts={togglePopoverActiveProducts}
                                    selectedProductsType={selectedProductsType}
                                  />
                                }
                                autofocusTarget="first-node"
                                onClose={togglePopoverActiveProducts}
                              >
                                <OptionListProductsType
                                  selectedProductsType={selectedProductsType}
                                  setSelectedProductsType={setSelectedProductsType}
                                  togglePopoverActiveProducts={togglePopoverActiveProducts}
                                />
                              </Popover>
                            </BlockStack>
                          </Box>
                        </BlockStack>
                        <Box padding={"400"}>
                          {selectedProductsType[0] == "automatic_products" ? (
                            <FreeUpsellAutomaticProd
                              data={productsData}
                              handleChangeData={handleChangeData}
                              handleChangesOpenHiddenProductModal={handleChangesOpenHiddenProductModal}
                              handleChangesClearHiddenProduct={handleChangesClearHiddenProduct}
                              selectedHiddenProducts={selectedHiddenProducts}
                            />
                          ) : selectedProductsType[0] == "collection" ? (
                            <FreeUpsellCollection
                              handleChangesOpenCollectionModal={handleChangesOpenCollectionModal}
                              selectedCollections={selectedCollections}
                              handleRemoveCollection={handleRemoveCollection}
                              shop={shop}
                            />
                          ) : (
                            <FreeUpsellmanualProducts
                              handleChangesOpenManualProductModal={handleChangesOpenManualProductModal}
                              selectedManualProducts={selectedManualProducts}
                              handleRemoveManualProducts={handleRemoveManualProducts}
                            />
                          )}
                        </Box>
                      </BlockStack>
                    </Box>
                  </BlockStack>
                </BlockStack>
              </Card>
            </Layout.Section>
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

const ProductPopoverActivator = ({ togglePopoverActiveProducts, selectedProductsType }) => (
  <Box onClick={togglePopoverActiveProducts}>
    <div className="hover:bg-[bg-surface-hover]">
      <Box borderColor="border" borderRadius="200" borderWidth="025">
        <Box padding={"400"}>
          <BlockStack gap={"200"}>
            <InlineStack blockAlign="center" align="space-between">
              <InlineStack gap={"300"} blockAlign="center" wrap>
                <InlineStack wrap>
                  <Box background="bg" borderColor="transparent" borderStyle="solid" borderRadius="150" borderWidth="025" padding={"150"}>
                    <span class="Polaris-Icon Polaris-Icon--toneSubdued">
                      {selectedProductsType[0] == "automatic_products" ? (
                        <svg viewBox="1 1 18 18" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                          <path d="M9.377 2.5c-.926 0-1.676.75-1.676 1.676v.688c0 .056-.043.17-.198.251-.153.08-.303.168-.448.262-.147.097-.268.076-.318.048l-.6-.346a1.676 1.676 0 0 0-2.29.613l-.622 1.08a1.676 1.676 0 0 0 .613 2.289l.648.374c.048.028.124.12.119.29a5.484 5.484 0 0 0 .005.465c.009.175-.07.27-.119.299l-.653.377a1.676 1.676 0 0 0-.613 2.29l.623 1.08a1.676 1.676 0 0 0 2.29.613l.7-.405c.048-.028.166-.048.312.043.115.071.233.139.353.202.155.08.198.195.198.251v.811c0 .926.75 1.676 1.676 1.676h1.246c.926 0 1.676-.75 1.676-1.676v-.81a.75.75 0 0 0-1.5 0v.81a.176.176 0 0 1-.176.176h-1.246a.176.176 0 0 1-.176-.176v-.81c0-.73-.462-1.3-1.003-1.582a3.873 3.873 0 0 1-.255-.146c-.514-.32-1.23-.428-1.855-.068l-.7.405a.176.176 0 0 1-.241-.065l-.623-1.08a.176.176 0 0 1 .064-.24l.653-.377c.637-.368.899-1.062.867-1.677a3.97 3.97 0 0 1-.004-.337c.02-.604-.245-1.278-.868-1.638l-.648-.374a.176.176 0 0 1-.064-.24l.623-1.08a.176.176 0 0 1 .24-.064l.6.346c.638.368 1.37.247 1.888-.09a3.85 3.85 0 0 1 .323-.19c.54-.282 1.003-.852 1.003-1.58v-.688c0-.097.078-.176.176-.176h1.246c.097 0 .176.079.176.176v.688c0 .728.462 1.298 1.003 1.58.11.058.219.122.323.19.517.337 1.25.458 1.888.09l.6-.346a.176.176 0 0 1 .24.064l.623 1.08a.176.176 0 0 1-.064.24l-.648.374c-.623.36-.888 1.034-.868 1.638l.002.128c0 .082-.002.248-.006.309a.75.75 0 0 0 1.498.078 9.926 9.926 0 0 0 .005-.563c-.005-.171.07-.263.12-.291l.647-.374a1.676 1.676 0 0 0 .613-2.29l-.623-1.079a1.676 1.676 0 0 0-2.29-.613l-.6.346c-.049.028-.17.048-.318-.048a5.4 5.4 0 0 0-.448-.262c-.155-.081-.197-.195-.197-.251v-.688c0-.926-.75-1.676-1.676-1.676h-1.246Z"></path>
                          <path fill-rule="evenodd" d="M10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm0-1.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"></path>
                          <path d="M14.035 11.839a.5.5 0 0 0-.785.411v4.5a.5.5 0 0 0 .785.411l3.25-2.25a.5.5 0 0 0 0-.822l-3.25-2.25Z"></path>
                        </svg>
                      ) : selectedProductsType[0] == "collection" ? (
                        <svg viewBox="1 1 18 18" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                          <path
                            fill-rule="evenodd"
                            d="M16 5.5c.414 0 .75.336.75.75v2.964c0 .862-.342 1.69-.952 2.299l-.018.017c-.293.293-.767.293-1.06 0-.293-.293-.293-.767 0-1.06l.017-.018c.329-.328.513-.773.513-1.238v-2.964c0-.414.336-.75.75-.75Z"
                          ></path>
                          <path
                            fill-rule="evenodd"
                            d="M6.767 4.098c.703-.703 1.657-1.098 2.652-1.098h2.081c1.519 0 2.75 1.231 2.75 2.75v2.289c0 .861-.342 1.688-.952 2.298l-4.206 4.206c-.976.976-2.56.976-3.536 0l-2.672-2.672c-1.074-1.075-1.074-2.816 0-3.89l3.883-3.883Zm2.652.402c-.597 0-1.17.237-1.591.659l-3.883 3.883c-.489.488-.489 1.28 0 1.768l2.672 2.672c.39.39 1.024.39 1.414 0l4.206-4.206c.329-.328.513-.773.513-1.237v-2.289c0-.69-.56-1.25-1.25-1.25h-2.081Z"
                          ></path>
                          <path d="M11.75 6.5c0 .552-.448 1-1 1s-1-.448-1-1 .448-1 1-1 1 .448 1 1Z"></path>
                          <path d="M11.5 13.75c0-.414.336-.75.75-.75h4.5c.414 0 .75.336.75.75s-.336.75-.75.75h-4.5c-.414 0-.75-.336-.75-.75Z"></path>
                          <path d="M11.5 16.75c0-.414.336-.75.75-.75h4.5c.414 0 .75.336.75.75s-.336.75-.75.75h-4.5c-.414 0-.75-.336-.75-.75Z"></path>
                        </svg>
                      ) : (
                        <svg viewBox="1 1 18 18" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                          <path d="M11.276 3.5a3.75 3.75 0 0 0-2.701 1.149l-4.254 4.417a2.75 2.75 0 0 0 .036 3.852l2.898 2.898a2.5 2.5 0 0 0 3.502.033l.45-.434a.75.75 0 1 0-1.04-1.08l-.45.434a1 1 0 0 1-1.401-.014l-2.898-2.898a1.25 1.25 0 0 1-.016-1.75l4.253-4.418a2.25 2.25 0 0 1 1.62-.689h1.975c.966 0 1.75.784 1.75 1.75v2.371c0 .358-.146.7-.403.948a.75.75 0 1 0 1.04 1.08 2.81 2.81 0 0 0 .863-2.028v-2.371a3.25 3.25 0 0 0-3.25-3.25h-1.974Z"></path>
                          <path d="M13 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"></path>
                          <path d="M14.75 12a.75.75 0 0 1 .75.75v1.25h1.25a.75.75 0 0 1 0 1.5h-1.25v1.25a.75.75 0 0 1-1.5 0v-1.25h-1.25a.75.75 0 0 1 0-1.5h1.25v-1.25a.75.75 0 0 1 .75-.75Z"></path>
                        </svg>
                      )}
                    </span>
                  </Box>
                </InlineStack>
                <Text variant="headingSm">
                  {selectedProductsType[0] == "automatic_products"
                    ? "Automatic products"
                    : selectedProductsType[0] == "collection"
                    ? "Collection"
                    : "Manual product"}
                </Text>
              </InlineStack>
              <Button
                size="large"
                variant="monochromePlain"
                textAlign="center"
                icon={
                  <span class="Polaris-Icon">
                    <svg viewBox="1 1 18 18" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                      <path d="M10.884 4.323a1.25 1.25 0 0 0-1.768 0l-2.646 2.647a.75.75 0 0 0 1.06 1.06l2.47-2.47 2.47 2.47a.75.75 0 1 0 1.06-1.06l-2.646-2.647Z"></path>
                      <path d="m13.53 13.03-2.646 2.647a1.25 1.25 0 0 1-1.768 0l-2.646-2.647a.75.75 0 0 1 1.06-1.06l2.47 2.47 2.47-2.47a.75.75 0 0 1 1.06 1.06Z"></path>
                    </svg>
                  </span>
                }
              />
            </InlineStack>
          </BlockStack>
        </Box>
      </Box>
    </div>
  </Box>
);

const OptionListProductsType = ({ setSelectedProductsType, selectedProductsType, togglePopoverActiveProducts }) => (
  <OptionList
    onChange={(selected) => {
      setSelectedProductsType(selected);
      togglePopoverActiveProducts();
    }}
    options={[
      {
        value: "automatic_products",
        label: (
          <>
            <BlockStack>
              <InlineStack blockAlign="center" gap={"050"}>
                <Text variant="headingSm">Automatic products</Text>
              </InlineStack>
              <BlockStack>
                <InlineStack blockAlign="center" gap={"050"}>
                  <Text tone="subdued">Leverage Shopify's product recommendations.</Text>
                </InlineStack>
              </BlockStack>
            </BlockStack>
          </>
        ),
        media: (
          <span class="Polaris-Icon Polaris-Icon--toneSubdued">
            <svg viewBox="1 1 18 18" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
              <path d="M9.377 2.5c-.926 0-1.676.75-1.676 1.676v.688c0 .056-.043.17-.198.251-.153.08-.303.168-.448.262-.147.097-.268.076-.318.048l-.6-.346a1.676 1.676 0 0 0-2.29.613l-.622 1.08a1.676 1.676 0 0 0 .613 2.289l.648.374c.048.028.124.12.119.29a5.484 5.484 0 0 0 .005.465c.009.175-.07.27-.119.299l-.653.377a1.676 1.676 0 0 0-.613 2.29l.623 1.08a1.676 1.676 0 0 0 2.29.613l.7-.405c.048-.028.166-.048.312.043.115.071.233.139.353.202.155.08.198.195.198.251v.811c0 .926.75 1.676 1.676 1.676h1.246c.926 0 1.676-.75 1.676-1.676v-.81a.75.75 0 0 0-1.5 0v.81a.176.176 0 0 1-.176.176h-1.246a.176.176 0 0 1-.176-.176v-.81c0-.73-.462-1.3-1.003-1.582a3.873 3.873 0 0 1-.255-.146c-.514-.32-1.23-.428-1.855-.068l-.7.405a.176.176 0 0 1-.241-.065l-.623-1.08a.176.176 0 0 1 .064-.24l.653-.377c.637-.368.899-1.062.867-1.677a3.97 3.97 0 0 1-.004-.337c.02-.604-.245-1.278-.868-1.638l-.648-.374a.176.176 0 0 1-.064-.24l.623-1.08a.176.176 0 0 1 .24-.064l.6.346c.638.368 1.37.247 1.888-.09a3.85 3.85 0 0 1 .323-.19c.54-.282 1.003-.852 1.003-1.58v-.688c0-.097.078-.176.176-.176h1.246c.097 0 .176.079.176.176v.688c0 .728.462 1.298 1.003 1.58.11.058.219.122.323.19.517.337 1.25.458 1.888.09l.6-.346a.176.176 0 0 1 .24.064l.623 1.08a.176.176 0 0 1-.064.24l-.648.374c-.623.36-.888 1.034-.868 1.638l.002.128c0 .082-.002.248-.006.309a.75.75 0 0 0 1.498.078 9.926 9.926 0 0 0 .005-.563c-.005-.171.07-.263.12-.291l.647-.374a1.676 1.676 0 0 0 .613-2.29l-.623-1.079a1.676 1.676 0 0 0-2.29-.613l-.6.346c-.049.028-.17.048-.318-.048a5.4 5.4 0 0 0-.448-.262c-.155-.081-.197-.195-.197-.251v-.688c0-.926-.75-1.676-1.676-1.676h-1.246Z"></path>
              <path fill-rule="evenodd" d="M10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm0-1.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"></path>
              <path d="M14.035 11.839a.5.5 0 0 0-.785.411v4.5a.5.5 0 0 0 .785.411l3.25-2.25a.5.5 0 0 0 0-.822l-3.25-2.25Z"></path>
            </svg>
          </span>
        ),
      },
      {
        value: "collection",
        label: (
          <>
            <BlockStack>
              <InlineStack blockAlign="center" gap={"050"}>
                <Text variant="headingSm">Collection</Text>
              </InlineStack>
              <BlockStack>
                <InlineStack blockAlign="center" gap={"050"}>
                  <Text tone="subdued">Show products from a collection.</Text>
                </InlineStack>
              </BlockStack>
            </BlockStack>
          </>
        ),
        media: (
          <span class="Polaris-Icon Polaris-Icon--toneSubdued">
            <svg viewBox="1 1 18 18" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
              <path
                fill-rule="evenodd"
                d="M16 5.5c.414 0 .75.336.75.75v2.964c0 .862-.342 1.69-.952 2.299l-.018.017c-.293.293-.767.293-1.06 0-.293-.293-.293-.767 0-1.06l.017-.018c.329-.328.513-.773.513-1.238v-2.964c0-.414.336-.75.75-.75Z"
              ></path>
              <path
                fill-rule="evenodd"
                d="M6.767 4.098c.703-.703 1.657-1.098 2.652-1.098h2.081c1.519 0 2.75 1.231 2.75 2.75v2.289c0 .861-.342 1.688-.952 2.298l-4.206 4.206c-.976.976-2.56.976-3.536 0l-2.672-2.672c-1.074-1.075-1.074-2.816 0-3.89l3.883-3.883Zm2.652.402c-.597 0-1.17.237-1.591.659l-3.883 3.883c-.489.488-.489 1.28 0 1.768l2.672 2.672c.39.39 1.024.39 1.414 0l4.206-4.206c.329-.328.513-.773.513-1.237v-2.289c0-.69-.56-1.25-1.25-1.25h-2.081Z"
              ></path>
              <path d="M11.75 6.5c0 .552-.448 1-1 1s-1-.448-1-1 .448-1 1-1 1 .448 1 1Z"></path>
              <path d="M11.5 13.75c0-.414.336-.75.75-.75h4.5c.414 0 .75.336.75.75s-.336.75-.75.75h-4.5c-.414 0-.75-.336-.75-.75Z"></path>
              <path d="M11.5 16.75c0-.414.336-.75.75-.75h4.5c.414 0 .75.336.75.75s-.336.75-.75.75h-4.5c-.414 0-.75-.336-.75-.75Z"></path>
            </svg>
          </span>
        ),
      },
      {
        value: "manual_product",
        label: (
          <>
            <BlockStack>
              <InlineStack blockAlign="center" gap={"050"}>
                <Text variant="headingSm">Manual product</Text>
              </InlineStack>
              <BlockStack>
                <InlineStack blockAlign="center" gap={"050"}>
                  <Text tone="subdued">Show specific products.</Text>
                </InlineStack>
              </BlockStack>
            </BlockStack>
          </>
        ),
        media: (
          <span class="Polaris-Icon Polaris-Icon--toneSubdued">
            <svg viewBox="1 1 18 18" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
              <path d="M11.276 3.5a3.75 3.75 0 0 0-2.701 1.149l-4.254 4.417a2.75 2.75 0 0 0 .036 3.852l2.898 2.898a2.5 2.5 0 0 0 3.502.033l.45-.434a.75.75 0 1 0-1.04-1.08l-.45.434a1 1 0 0 1-1.401-.014l-2.898-2.898a1.25 1.25 0 0 1-.016-1.75l4.253-4.418a2.25 2.25 0 0 1 1.62-.689h1.975c.966 0 1.75.784 1.75 1.75v2.371c0 .358-.146.7-.403.948a.75.75 0 1 0 1.04 1.08 2.81 2.81 0 0 0 .863-2.028v-2.371a3.25 3.25 0 0 0-3.25-3.25h-1.974Z"></path>
              <path d="M13 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"></path>
              <path d="M14.75 12a.75.75 0 0 1 .75.75v1.25h1.25a.75.75 0 0 1 0 1.5h-1.25v1.25a.75.75 0 0 1-1.5 0v-1.25h-1.25a.75.75 0 0 1 0-1.5h1.25v-1.25a.75.75 0 0 1 .75-.75Z"></path>
            </svg>
          </span>
        ),
      },
    ]}
    selected={selectedProductsType}
  />
);

const FreeUpsellCollection = ({ handleChangesOpenCollectionModal, selectedCollections, handleRemoveCollection, shop }) =>
  selectedCollections !== null ? (
    <InlineGrid columns="1fr auto" alignItems="center">
      <InlineStack blockAlign="center" gap={"400"}>
        <Thumbnail source={CollectionIcon} size="medium" />
        <Text fontWeight="semibold">
          <Link removeUnderline url={`https://${shop}/collections/${selectedCollections?.handle}`} target="_blank">
            {selectedCollections?.title}
          </Link>
        </Text>
      </InlineStack>
      <BlockStack gap={"400"}>
        <Button
          variant="tertiary"
          size="medium"
          textAlign="center"
          icon={ViewIcon}
          url={`https://${shop}/collections/${selectedCollections?.handle}`}
          target="_blank"
        />
        <Button
          variant="tertiary"
          size="medium"
          textAlign="center"
          tone="critical"
          icon={
            <span class="Polaris-Icon">
              <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                <path d="M12.72 13.78a.75.75 0 1 0 1.06-1.06l-2.72-2.72 2.72-2.72a.75.75 0 0 0-1.06-1.06l-2.72 2.72-2.72-2.72a.75.75 0 0 0-1.06 1.06l2.72 2.72-2.72 2.72a.75.75 0 1 0 1.06 1.06l2.72-2.72 2.72 2.72Z"></path>
              </svg>
            </span>
          }
          onClick={handleRemoveCollection}
        />
      </BlockStack>
    </InlineGrid>
  ) : (
    <Box background="bg-fill" borderColor="border" borderStyle="solid" borderWidth="025" borderRadius="200" padding={"800"}>
      <BlockStack inlineAlign="center" gap={"400"}>
        <Thumbnail source={CollectionIcon} size="medium" />
        <BlockStack gap={"100"}>
          <Text variant="headingLg">No collection selected</Text>
        </BlockStack>
        <Button variant="primary" size="medium" textAlign="center" onClick={handleChangesOpenCollectionModal}>
          <Text variant="bodySm" fontWeight="semibold">
            Select collection
          </Text>
        </Button>
      </BlockStack>
    </Box>
  );

const FreeUpsellmanualProducts = ({ handleChangesOpenManualProductModal, selectedManualProducts, handleRemoveManualProducts }) =>
  selectedManualProducts?.length ? (
    <Box>
      <Box background="bg-fill" borderColor="border" borderStyle="solid" borderWidth="025" borderRadius="200">
        <BlockStack>
          <div className="services_products_list">
            <ol
              style={{
                display: "flex",
                flexDirection: "column",
                listStyle: "none",
                margin: "0",
                padding: "0",
                borderRadius: "8px",
              }}
            >
              {selectedManualProducts?.map((data, i) => {
                return (
                  <li
                    key={i}
                    style={{
                      alignItems: "center",
                      display: "grid",
                      gridTemplateColumns: "auto auto 1fr auto",
                      padding: "var(--p-space-4) var(--p-space-5)",
                      position: "relative",
                      rowGap: "var(--p-space-5)",
                    }}
                  >
                    <div>
                      <LegacyStack alignment="center" wrap={false}>
                        <LegacyStack.Item>
                          <div>
                            <Thumbnail source={data?.image} size="small" />
                          </div>
                        </LegacyStack.Item>
                        <LegacyStack.Item fill>
                          <BlockStack gap={1}>
                            <Button variant="plain" textAlign="left">
                              {data?.title}
                            </Button>
                          </BlockStack>
                        </LegacyStack.Item>
                      </LegacyStack>
                    </div>
                    <div
                      className="close-btn"
                      style={{
                        gridColumn: "-2",
                        gridRow: "1/-1",
                      }}
                    >
                      <Button onClick={() => handleRemoveManualProducts(data?.shopify_product_id)} variant="plain" icon={XSmallIcon}></Button>
                    </div>
                  </li>
                );
              })}
            </ol>
          </div>
          <Box padding={"400"}>
            <InlineStack align="start">
              <Button variant="primary" size="medium" textAlign="center" onClick={handleChangesOpenManualProductModal}>
                <Text variant="bodySm" fontWeight="semibold">
                  Select product
                </Text>
              </Button>
            </InlineStack>
          </Box>
        </BlockStack>
      </Box>
    </Box>
  ) : (
    <Box background="bg-fill" borderColor="border" borderStyle="solid" borderWidth="025" borderRadius="200" padding={"800"}>
      <BlockStack inlineAlign="center" gap={"400"}>
        <Thumbnail source={ProductIcon} size="medium" />
        <BlockStack gap={"100"}>
          <Text variant="headingLg">No product selected</Text>
        </BlockStack>
        <Button variant="primary" size="medium" textAlign="center" onClick={handleChangesOpenManualProductModal}>
          <Text variant="bodySm" fontWeight="semibold">
            Select product
          </Text>
        </Button>
      </BlockStack>
    </Box>
  );
