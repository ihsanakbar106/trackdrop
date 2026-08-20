import React, { useCallback, useContext, useEffect, useState } from "react";
import {
  Badge,RadioButton,EmptyState,
  Button,InlineGrid,
  Card,Select,
  ChoiceList,
  Icon,
  IndexFilters,
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
  Box,
  Divider,
  ButtonGroup,
  Popover,
  ActionList,
  Modal,
  TextContainer,
  Frame,
  Banner,
  Scrollable,
  Toast,
  SkeletonThumbnail,
  SkeletonBodyText,
  SkeletonDisplayText,
} from "@shopify/polaris";
import {
  LanguageTranslateIcon
} from '@shopify/polaris-icons';
import { ThemeIcon, ViewIcon } from "@shopify/polaris-icons";
import Stepper from "../../components/Stepper";
import { useNavigate } from "react-router-dom";
import { useAppBridge } from "@shopify/app-bridge-react";
import { AppContext } from "../../components";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import Modern from "../../assets/modren_tracking_template.png";
import ShopifyImage from "../../assets/traditional_traking_template.png";
// import Modern from "../../assets/Modern.png";
// import ShopifyImage from "../../assets/ShopifyImage.png";
import NoPage from "../../assets/no-results.png";
import NoTrackingPage from "../../assets/no_tracking_pages.png";

const generateSlug = (text) => {
  return text
    .toLowerCase() // Convert to lowercase
    .replace(/\s+/g, "-") // Replace spaces with hyphens
    .replace(/[^\w\-]+/g, "") // Remove special characters
    .replace(/--+/g, "-") // Replace multiple hyphens with a single hyphen
    .trim(); // Trim leading and trailing hyphens
};

const formatDate = (dateString) => {
  const date = new Date(dateString);

  const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
  const month = months[date.getMonth()];
  const day = date.getDate();

  let hours = date.getHours();
  const minutes = date.getMinutes();
  const ampm = hours >= 12 ? "pm" : "am";
  hours = hours % 12;
  hours = hours ? hours : 12; // the hour '0' should be '12'
  const minutesStr = minutes < 10 ? "0" + minutes : minutes;

  return `${month} ${day} at ${hours}:${minutesStr} ${ampm}`;
};

export default function TrackingPages() {
  const navigate = useNavigate();
  const appBridge = useAppBridge();
  const { apiUrl, shop, setModernTrackingPageStyle, setModernTrackingPageTitle, setModernTrackingPageHandle, setTrackingPageType } = useContext(AppContext);
  const [toggleData, setToggleData] = useState(true);
  const [loading, setLoading] = useState(true);
  const [btnLoading, setBtnLoading] = useState(false);
  const [errorToast, setErrorToast] = useState(false);
  const [successToast, setSuccessToast] = useState(false);
  const [toastMsg, setToastMsg] = useState("");
  const [popoverActive, setPopoverActive] = useState(false);
  const [activeDNDModal, setActiveDNDModal] = useState(false);
  const [activeModernModal, setActiveModernModal] = useState(false);
  const [dndCurrentStep, setDNDCurrentStep] = useState(3);
  const [modernCurrentStep, setModernCurrentStep] = useState(1);
  const languages = [
    { value: "en", label: "English (en)" },
    { value: "he", label: "Hebrew (he)" },
  ];
  const [allLanguages, setAllLanguages] = useState(languages);
  const [allTranslations, setAllTranslations] = useState({});
  const [translation, setTranslation] = useState({});

  const [defaultLanguage, setDefaultLanguage] = useState({});
  const [addTranslationModal, setAddTranslationModal] = useState(false);
  const [editTranslationModal, setEditTranslationModal] = useState(false);
  const handleAddTranslationModal = useCallback(() => setAddTranslationModal(!addTranslationModal), [addTranslationModal]);
  const handleEditTranslationModal = useCallback(() => setEditTranslationModal(!editTranslationModal), [editTranslationModal]);

  const [dndData, setDndData] = useState({
    page_title: "",
    page_URL_handle: "",
  });
  const [dndTitleError, setDNDTitleError] = useState(false);

  // console.log("dndData", dndData);
  const [dndStyle, setDndStyle] = useState("");
  const [dndLayout, setDndLayout] = useState("");

  const [modernStyle, setModernStyle] = useState("Light");
  const [modernData, setModernData] = useState({
    page_title: "",
  });
  const [modernTitleError, setModernTitleError] = useState(false);
  const [themeType, setThemeType] = useState("Traditional");

  const [trackingPages, setTrackingPages] = useState([]);
  const [currentTrackingPage, setCurrentTrackingPage] = useState("");
  const [popovers, setPopovers] = useState(new Array(trackingPages.length).fill(false));

  const [activeDuplicateModal, setActiveDuplicateModal] = useState(false);
  const [activeDeleteModal, setActiveDeleteModal] = useState(false);
  const [pageId, setPageId] = useState("");
  const [themeId, setThemeId] = useState("");

  const [duplicatePageTitle, setDuplicatePageTitle] = useState("");
  const [duplicatePageTitleError, setDuplicatePageTitleError] = useState(false);

  /*console.log("trackingPages: ", trackingPages);
  console.log("currentTrackingPage: ", currentTrackingPage);
  console.log("pageId: ", pageId);*/

  const DND_NUMBER_OF_STEPS = 4;
  const MODERN_NUMBER_OF_STEPS = 2;

  const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
  const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

  const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

  const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

  const DNDStepOne = (handleDndStyle) => {
    return (
        <>
          <BlockStack gap={"100"}>
            <Text fontWeight="semibold">
              Step 1 of 3: Select the style that complements your store No worries, you can customize colors and more later. Let's begin!
            </Text>
            <Text as="span" variant="bodyMd" tone="subdued">
              Don't worry you will be able to change the colors and everything later on, we just getting you started
            </Text>
          </BlockStack>
          <div className="nYb5p">
            <div className="hT2vd" onClick={() => handleDndStyle("Simple")}>
              <Card padding={"0"}>
                <div className="QOdNm">
                  <img src="https://cdn.shopify.com/theme-store/zx2c5zmpirbt8e0j4p4smtb1oa2d.jpg" alt="Screenshot of the Dawn theme"></img>
                </div>
                <div className="wzpgr">
                  <Box padding={"400"}>
                    <Text fontWeight="semibold">Simple</Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Smooth corners, making it ideal
                    </Text>
                  </Box>
                </div>
              </Card>
            </div>
            <div className="hT2vd" onClick={() => handleDndStyle("Elegant")}>
              <Card padding={"0"}>
                <div className="QOdNm">
                  <img src="https://cdn.shopify.com/theme-store/zx2c5zmpirbt8e0j4p4smtb1oa2d.jpg" alt="Screenshot of the Dawn theme"></img>
                </div>
                <div className="wzpgr">
                  <Box padding={"400"}>
                    <Text fontWeight="semibold">Elegant</Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Straight corners and borders
                    </Text>
                  </Box>
                </div>
              </Card>
            </div>
            <div className="hT2vd" onClick={() => handleDndStyle("Playful")}>
              <Card padding={"0"}>
                <div className="QOdNm">
                  <img src="https://cdn.shopify.com/theme-store/zx2c5zmpirbt8e0j4p4smtb1oa2d.jpg" alt="Screenshot of the Dawn theme"></img>
                </div>
                <div className="wzpgr">
                  <Box padding={"400"}>
                    <Text fontWeight="semibold">Playful</Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Extra curves creating energized feeling
                    </Text>
                  </Box>
                </div>
              </Card>
            </div>
          </div>

        </>
    );
  };

  const DNDStepTwo = (handleDndLayout) => {
    return (
        <>
          <Text fontWeight="semibold">Step 2 of 3: Choose your layout</Text>
          <div className="nYb5p">
            <div className="hT2vd" onClick={() => handleDndLayout("Minimal")}>
              <Card padding={"0"}>
                <div className="QOdNm">
                  <img className="w-full" src="https://assets.rush.app/app/os2-tracking-page/layout_minimal.jpg" alt="Screenshot of the Dawn theme"></img>
                </div>
                <div className="wzpgr">
                  <Box padding={"400"}>
                    <Text fontWeight="semibold">Minimal</Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Just a tracking page
                    </Text>
                  </Box>
                </div>
              </Card>
            </div>
            <div className="hT2vd" onClick={() => handleDndLayout("Standard")}>
              <Card padding={"0"}>
                <div className="QOdNm">
                  <img className="w-full" src="https://assets.rush.app/app/os2-tracking-page/layout_all.jpg" alt="Screenshot of the Dawn theme"></img>
                </div>
                <div className="wzpgr">
                  <Box padding={"400"}>
                    <Text fontWeight="semibold">Standard</Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Tracking page with banner and upsells
                    </Text>
                  </Box>
                </div>
              </Card>
            </div>
            <div className="hT2vd" onClick={() => handleDndLayout("I want it all")}>
              <Card padding={"0"}>
                <div className="QOdNm">
                  <img className="w-full" src="https://assets.rush.app/app/os2-tracking-page/layout_all.jpg" alt="Screenshot of the Dawn theme"></img>
                </div>
                <div className="wzpgr">
                  <Box padding={"400"}>
                    <Text fontWeight="semibold">I want it all</Text>
                    <Text as="span" variant="bodyMd" tone="subdued">
                      Tacking page with all the bells and whistles
                    </Text>
                  </Box>
                </div>
              </Card>
            </div>
          </div>
        </>
    );
  };

  const DNDStepThree = (shop, dndData, dndTitleError, handleChangeDndData,themeType) => {
    return (
        <>
          <Text fontWeight="semibold"> Pick tracking page url</Text>
          <Banner tone="warning">
            <p>If a page with an identical URL exists, we will replace it with</p>
          </Banner>
          <TextField
              label="Page title (only English)"
              value={dndData?.page_title}
              onChange={(value) => handleChangeDndData("page_title", value)}
              autoComplete="off"
              error={dndTitleError ? "Page title is required" : ""}
          />
          <TextField
              label="URL and handle"
              value={dndData?.page_URL_handle}
              prefix={`https://${shop}/pages/`}
              onChange={(value) => handleChangeDndData("page_URL_handle", value)}
              autoComplete="off"
          />
          <div className="customRadio">
            <InlineGrid gap="100" columns={2}>
              <RadioButton
                  label={
                    <>
                      <div className="hT2vd"  style={{
                        width: "100%",
                        height: "auto",
                        borderRadius:"10px",
                        border: themeType === 'Traditional' ? '3px solid black' : '3px solid #cccccc'
                      }}>
                        <Card padding={"0"}>
                          <div className="QOdNm">
                            <Link target="_blank">
                              <img src="https://cdn.shopify.com/theme-store/zx2c5zmpirbt8e0j4p4smtb1oa2d.jpg" alt="Screenshot of the Dawn theme"></img>
                            </Link>
                          </div>
                          <div className="wzpgr">
                            <Box padding={"400"}>
                              <InlineStack align="space-between" wrap={false} blockAlign="center">
                                <Box>
                                  <Text fontWeight="semibold">Traditional</Text>
                                  <Text as="span" variant="bodyMd" tone="subdued">
                                    Online Store Editor with effortless customization
                                  </Text>
                                </Box>
                              </InlineStack>
                            </Box>
                          </div>
                        </Card>
                      </div>
                    </>}
                  checked={themeType === 'Traditional'}
                  id="default"
                  name="map_theme"
                  onChange={() => handleThemeType(true, 'Traditional')}
                  value={themeType}
                  // helpText="Default"
              />
              <RadioButton
                  label={
                    <>
                      <div className="hT2vd" style={{
                        width: "100%",
                        height: "auto",
                        borderRadius:"10px",
                        border: themeType === 'Modern' ? '3px solid black' : '3px solid #cccccc'
                      }}>
                        <Card padding={"0"}>
                          <div className="QOdNm">
                            <Link target="_blank">
                              <img src="https://cdn.shopify.com/theme-store/zx2c5zmpirbt8e0j4p4smtb1oa2d.jpg" alt="Screenshot of the Dawn theme"></img>
                            </Link>
                          </div>
                          <div className="wzpgr">
                            <Box padding={"400"}>
                              <InlineStack align="space-between" wrap={false} blockAlign="center">
                                <Box>
                                  <Text fontWeight="semibold">Modern</Text>
                                  <Text as="span" variant="bodyMd" tone="subdued">
                                    Tailored for an exceptional mobile experience
                                  </Text>
                                </Box>
                              </InlineStack>
                            </Box>
                          </div>
                        </Card>
                      </div>
                    </>}
                  checked={themeType === 'Modern'}
                  id="default"
                  name="map_theme"
                  onChange={() => handleThemeType(true, 'Modern')}
                  value={themeType}
                  // helpText="Default"
              />

            </InlineGrid>
          </div>
        </>
    );
  };

  const ModernStepOne = (handleModernStyle) => {
    return (
        <>
          <Text fontWeight="semibold">Step 1 of 2: Choose the style that complements your store's aesthetic</Text>
          <div className="nYb5p">
            <div className="hT2vd" onClick={() => handleModernStyle("Dark")}>
              <Card padding={"0"}>
                <div className="QOdNm">
                  <img src="https://cdn.shopify.com/theme-store/zx2c5zmpirbt8e0j4p4smtb1oa2d.jpg" alt="Screenshot of the Dawn theme"></img>
                </div>
                <div className="wzpgr">
                  <Box padding={"400"}>
                    <Text fontWeight="semibold">Dark</Text>
                  </Box>
                </div>
              </Card>
            </div>
            <div className="hT2vd" onClick={() => handleModernStyle("Light")}>
              <Card padding={"0"}>
                <div className="QOdNm">
                  <img src="https://cdn.shopify.com/theme-store/zx2c5zmpirbt8e0j4p4smtb1oa2d.jpg" alt="Screenshot of the Dawn theme"></img>
                </div>
                <div className="wzpgr">
                  <Box padding={"400"}>
                    <Text fontWeight="semibold">Light</Text>
                  </Box>
                </div>
              </Card>
            </div>
          </div>
        </>
    );
  };

  const ModernStepThree = (shop, dndData, modernTitleError, handleChangeModernData) => {
    return (
        <>
          <Text fontWeight="semibold">Step 2 of 2: Pick tracking page title</Text>
          <TextField
              label="Page title (only English)"
              value={dndData?.page_title}
              onChange={(value) => handleChangeModernData("page_title", value)}
              autoComplete="off"
              error={modernTitleError ? "Page title is required" : ""}
          />
          <TextField disabled label="URL and handle" prefix={`https://${shop}/a/track/order`} autoComplete="off" />
        </>
    );
  };
  const getSelectedLabel = (defaultLanguage) => {
    const selectedLanguage = languages.find(
        (language) => language.value === defaultLanguage
    );

    return selectedLanguage ? selectedLanguage.label : "";
  };
  useEffect(() => {
    if (Array.isArray(allTranslations)) {
      const selectedTranslation = allTranslations.find(
          (translation) => translation.language === defaultLanguage
      );
      setTranslation(selectedTranslation || null);
    }
  }, [defaultLanguage, allTranslations]);

  const handleChangeTranslation = useCallback(
      (keyword,newValue) => {
        setTranslation((prevValues) => ({
          ...prevValues,
          [keyword]: newValue
        }));
      },
      []
  );
  const handleChangeLang = (value) => {
    setDefaultLanguage(value);
  };
  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}tracking-pages`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { plan_id,tracking_pages, active_theme_id,translations } = response?.data;
      if(!plan_id){
        navigate("/billing");
      }
      // setTrackingPages(tracking_pages?.filter((page) => page?.active_status === 0) || []);
      setTrackingPages(tracking_pages);
      setAllTranslations(translations);


      const defaultTranslation = translations.find(
          (translation) => translation.is_default === 1
      );
      const defaultLanguage = defaultTranslation ? defaultTranslation.language : null;

      setDefaultLanguage(defaultLanguage);
      setTranslation(defaultTranslation);
      // setCurrentTrackingPage(tracking_pages?.find((page) => page?.active_status === 1) || "");
      setThemeId(active_theme_id || "");
      setBtnLoading(false);
      setActiveDeleteModal(false);
      setActiveDuplicateModal(false);
      setDuplicatePageTitle("");
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
      setToggleData(false);
      setBtnLoading(false);
    }
  };
  const handleSubmit = async () => {


    setBtnLoading(true);
    try {
      let sessionToken = await getSessionToken(appBridge);
      const payload = {
        translation: translation,
      };

      const response = await axios.post(`${apiUrl}save-translation`, payload, {
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
    if (toggleData) {
      fetchData();
    }
  }, [toggleData]);

  const togglePopoverActive = (pageId) => {
    const newPopovers = [...popovers];
    newPopovers[pageId] = !newPopovers[pageId];
    setPopovers(newPopovers);
    setPageId(pageId);
  };

  // const handleChangeDNDModal = useCallback((type) => {
  //   setThemeType(type);
  //   setActiveDNDModal(true);
  // }, [])
  const handleChangeDNDModal = useCallback(() => {
    setActiveDNDModal(true);
  }, []);

  const handleThemeType = useCallback(
      (_checked, type) => setThemeType(type),
      [],
  );
  const handleChangeDNDModalClose = useCallback(() => {
    setActiveDNDModal(false);
    setDndData((prevState) => ({
      ...prevState,
      page_title: "",
      page_URL_handle: "",
    }));
    setDNDTitleError(false);
    setThemeType("Traditional");
    setDndStyle("");
    setDndLayout("");
    setDNDCurrentStep(3);
  }, []);

  const handleChangeModernModal = useCallback(() => {
    setActiveModernModal(!activeModernModal);
    setModernData((prevState) => ({
      ...prevState,
      page_title: "",
    }));
    setModernStyle("");
    setModernCurrentStep(1);
  }, [activeModernModal]);

  const dndGoToNextStep = () => setDNDCurrentStep((prev) => (prev === DND_NUMBER_OF_STEPS ? prev : prev + 1));
  const dndGoToPreviousStep = () => {
    setDNDCurrentStep((prev) => (prev === 1 ? prev : prev - 1));
    if (dndCurrentStep === 2) {
      setDndStyle("");
    } else if (dndCurrentStep === 3) {
      setDndLayout("");
    } else {
      setDndData((prevState) => ({
        ...prevState,
        page_title: "",
        page_URL_handle: "",
      }));
    }
  };

  const modernGoToNextStep = () => setModernCurrentStep((prev) => (prev === MODERN_NUMBER_OF_STEPS ? prev : prev + 1));
  const modernGoToPreviousStep = () => {
    setModernCurrentStep((prev) => (prev === 1 ? prev : prev - 1));
    if (modernCurrentStep === 2) {
      setModernData((prevState) => ({
        ...prevState,
        page_title: "",
      }));
    }
  };

  const handleChangeDndData = useCallback((field, value) => {
    setDndData((prevState) => ({
      ...prevState,
      [field]: value,
    }));
    if (field === "page_title") {
      setDndData((prevState) => ({
        ...prevState,
        page_URL_handle: generateSlug(value),
      }));
    }
  }, []);

  const handleDndStyle = (value) => {
    setDndStyle(value);
    dndGoToNextStep();
  };

  const handleDndLayout = (value) => {
    setDndLayout(value);
    dndGoToNextStep();
  };

  const handleModernStyle = (value) => {
    setModernStyle(value);
    modernGoToNextStep();
  };
  const handleChangeModernData = useCallback((field, value) => {
    setModernData((prevState) => ({
      ...prevState,
      [field]: value,
    }));
    setModernTitleError(false);
  }, []);

  const handleDuplicateModal = useCallback((id) => {
    setActiveDuplicateModal(true);
    setPageId(id);
  }, []);

  const handleDuplicateModalClose = useCallback(() => {
    setActiveDuplicateModal(false);
  }, []);

  const handleDeletePageModal = useCallback((id) => {
    setActiveDeleteModal(true);
    setPageId(id);
  }, []);

  const handleDeletePageModalClose = useCallback(() => {
    setActiveDeleteModal(false);
  }, []);

  const addPage = () => {
    if (modernData?.page_title?.trim() === "") {
      setModernTitleError(true);
      return;
    }
    setModernTrackingPageStyle(modernStyle);
    setModernTrackingPageTitle(modernData?.page_title);
    setModernTrackingPageHandle(modernData?.page_URL_handle);

    navigate("/tracking-page/customization");
  };

  const handleAddPage = async (btnLoading) => {
    if (dndData?.page_title?.trim() === "") {
      setDNDTitleError(true);
      return;
    }
    if (themeType=="Modern"){
      setModernTrackingPageStyle(modernStyle);
      setModernTrackingPageTitle(dndData?.page_title);
      setModernTrackingPageHandle(dndData?.page_URL_handle);
      setTrackingPageType(themeType);
      navigate("/tracking-page/customization");
      return;
    }

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
        theme_type: themeType,
        page_name: dndData?.page_title,
        data: {
          handle: dndData?.page_URL_handle,
          dnd_style: dndStyle,
          dnd_layout: dndLayout,
        },
      };

      const response = await axios.post(`${apiUrl}create-tracking-page`, payload, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      if (response?.data?.status === "success") {
        setDNDCurrentStep(4);
      }
      setToggleData(true);
    } catch (error) {
      setBtnLoading(false);
    }
  };

  const handleDeletePage = async (btnLoading) => {
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
      const response = await axios.get(`${apiUrl}delete-tracking-page/${pageId}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      // console.log("responseresponseresponseresponse", response);
      setSuccessToast(true);
      setToastMsg(response.data.message);
      togglePopoverActive(pageId);
    } catch (error) {
      setBtnLoading(false);
    } finally {
      setToggleData(true);
    }
  };

  const handleDuplicatePage = async (btnLoading) => {
    if (duplicatePageTitle?.trim() === "") {
      setDuplicatePageTitleError(true);
      return;
    }

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
      const response = await axios.get(`${apiUrl}duplicate-tracking-page/${pageId}?page_name=${duplicatePageTitle}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
    } catch (error) {
      setBtnLoading(false);
    } finally {
      setToggleData(true);
    }
  };

  const handlePublishPage = async (pageId) => {
    setBtnLoading((prev) => {
      let toggleId;
      if (prev[pageId]) {
        toggleId = { [pageId]: false };
      } else {
        toggleId = { [pageId]: true };
      }
      return { ...toggleId };
    });
    try {
      let sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}publish-tracking-page/${pageId}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      setSuccessToast(true);
      setToastMsg(response?.data?.message);
      setBtnLoading(false);
    } catch (error) {
      setBtnLoading(false);
    } finally {
      setToggleData(true);
    }
  };

  const editModernPage = () => {
    if (modernData?.page_title?.trim() === "") {
      setModernTitleError(true);
      return;
    }
    setModernTrackingPageStyle(modernStyle);
    setModernTrackingPageTitle(modernData?.page_title);
    setModernTrackingPageHandle(modernData?.page_URL_handle);

    navigate("/tracking-page/customization");
  };

  return (
    <Frame>
      <Page title="Tracking Pages">
        <Box>
        <Layout>
          {/*<Layout.Section variant="fullWidth">
            {currentTrackingPage !== "" ? (
              <Card padding={0}>
                <Box background="bg-surface-secondary">
                  {loading ? (
                    <Box padding={"400"}>
                      <SkeletonBodyText lines={12} />
                    </Box>
                  ) : (
                    <Box paddingBlockStart={"400"} paddingBlockEnd={"400"}>
                      <InlineStack align="center" blockAlign="center">
                        <img
                          alt={`Page preview thumbnail for ${currentTrackingPage?.page_name}`}
                          src={currentTrackingPage?.theme_type === "Traditional" ? ShopifyImage : Modern}
                          class="FZcd5 w-[400px] rounded"
                        />
                      </InlineStack>
                    </Box>
                  )}

                  <Divider borderColor="border-secondary" borderWidth="025" />
                  <Box background="bg-surface" padding={"500"}>
                    {loading ? (
                      <InlineStack align="space-between" blockAlign="center" gap={"400"} wrap={false}>
                        <SkeletonThumbnail size="large" />
                        <SkeletonBodyText />
                      </InlineStack>
                    ) : (
                      <InlineStack align="space-between" blockAlign="center" wrap={false}>
                        <InlineStack blockAlign="center" wrap={false} gap={"400"}>
                          <div class="vDAEg">
                            <img
                              style={{ aspectRatio: "5/4", objectFit: "contain" }}
                              alt={`Page preview thumbnail for ${currentTrackingPage?.page_name}`}
                              src={currentTrackingPage?.theme_type === "Traditional" ? ShopifyImage : Modern}
                            />
                          </div>
                          <BlockStack>
                            <LegacyStack spacing="tight">
                              <LegacyStack.Item>
                                <Text as="h3" variant="headingMd">
                                  {currentTrackingPage?.page_name}
                                </Text>
                              </LegacyStack.Item>
                              <LegacyStack.Item>
                                <Badge tone="success">
                                  <Text as="span" variant="bodySm">
                                    Current page
                                  </Text>
                                </Badge>
                              </LegacyStack.Item>
                            </LegacyStack>
                            <Text as="span" variant="bodyMd" tone="subdued">
                              Last saved: {formatDate(currentTrackingPage?.updated_at)}
                            </Text>
                            <Text as="span" variant="bodyMd" tone="subdued">
                              {currentTrackingPage?.theme_type === "Traditional" ? "Traditional" : "Modern"}
                            </Text>
                          </BlockStack>
                        </InlineStack>
                        <ButtonGroup>
                          <Popover
                            active={popovers[currentTrackingPage?.id]}
                            activator={
                              <Button
                                onClick={() => togglePopoverActive(currentTrackingPage?.id)}
                                size="medium"
                                textAlign="center"
                                icon={
                                  <span class="Polaris-Icon">
                                    <svg viewBox="0 0 20 20" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                                      <path d="M6 10a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"></path>
                                      <path d="M11.5 10a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"></path>
                                      <path d="M17 10a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"></path>
                                    </svg>
                                  </span>
                                }
                              ></Button>
                            }
                            autofocusTarget="first-node"
                            onClose={() => togglePopoverActive(currentTrackingPage?.id)}
                          >
                            <ActionList
                              actionRole="menuitem"
                              items={[
                                {
                                  content: "Preview",
                                  target: "_blank",
                                  url:
                                    currentTrackingPage?.theme_type === "Traditional"
                                      ? `https://${shop}/pages/${currentTrackingPage?.page_handle}`
                                      : "",
                                },
                                currentTrackingPage?.theme_type === "Traditional"
                                  ? {
                                      content: "Page edit",
                                      url: `https://${shop}/admin/pages/${currentTrackingPage?.shopify_page_id}`,
                                      target: "_blank",
                                    }
                                  : "",
                                {
                                  content: "Duplicate",
                                  onAction: () => handleDuplicateModal(currentTrackingPage?.id),
                                },
                              ]}
                            />
                          </Popover>
                          <Button
                            onClick={() => setTi(currentTrackingPage)}
                            target={currentTrackingPage?.theme_type === "Traditional" ? "_blank" : ""}
                            url={
                              currentTrackingPage?.theme_type === "Traditional"
                                ? `https://${shop}/admin/themes/${themeId}/editor?previewPath=/pages/${encodeURIComponent(
                                    currentTrackingPage?.page_handle,
                                  )}`
                                : `/tracking-page/customization/${currentTrackingPage?.id}`
                            }
                            size="medium"
                            textAlign="center"
                            variant="primary"
                          >
                            <Text as="span" variant="bodySm" fontWeight="medium">
                              Customize
                            </Text>
                          </Button>
                        </ButtonGroup>
                      </InlineStack>
                    )}
                  </Box>
                </Box>
              </Card>
            ) : (
              <Card padding={"600"}>
                <Box padding={"600"}>
                  <BlockStack inlineAlign="center">
                    <Box maxWidth="60%">
                      <BlockStack inlineAlign="center">
                        <BlockStack gap={300}>
                          <div className="flex justify-center items-center">
                            {" "}
                            <img src={NoPage} width={48} height={48} alt="" />
                          </div>
                          <Text as="p" variant="headingLg" alignment="center">
                            Currently No Active Page
                          </Text>
                          <Text as="p" variant="bodyMd" tone="subdued" alignment="center">
                            There are no active pages available at the moment. Please check back later or publish a new page to make it accessible.
                          </Text>
                        </BlockStack>
                      </BlockStack>
                    </Box>
                  </BlockStack>
                </Box>
              </Card>
            )}
          </Layout.Section>*/}
          <Layout.Section>
            <Card padding={0}>
              <Box padding={"400"}>
                <BlockStack>
                  <InlineStack align="space-between" blockAlign="center">
                    <Text as="h2" variant="headingMd">
                      Pages
                    </Text>
                  </InlineStack>
                  <Text as="span" variant="bodyMd" tone="subdued">
                    Manage your store's tracking pages. Add, customize and publish tracking pages.
                  </Text>
                </BlockStack>
              </Box>
              <Divider />
              {loading ? (
                <>
                  <Box background="bg-surface" padding={"500"}>
                    <InlineStack align="space-between" blockAlign="center" gap={"400"} wrap={false}>
                      <SkeletonThumbnail size="large" />
                      <SkeletonBodyText />
                    </InlineStack>
                  </Box>
                  <Divider />
                  <Box background="bg-surface" padding={"500"}>
                    <InlineStack align="space-between" blockAlign="center" gap={"400"} wrap={false}>
                      <SkeletonThumbnail size="large" />
                      <SkeletonBodyText />
                    </InlineStack>
                  </Box>
                  <Divider />
                  <Box background="bg-surface" padding={"500"}>
                    <InlineStack align="space-between" blockAlign="center" gap={"400"} wrap={false}>
                      <SkeletonThumbnail size="large" />
                      <SkeletonBodyText />
                    </InlineStack>
                  </Box>
                  <Divider />
                  <Box background="bg-surface" padding={"500"}>
                    <InlineStack align="space-between" blockAlign="center" gap={"400"} wrap={false}>
                      <SkeletonThumbnail size="large" />
                      <SkeletonBodyText />
                    </InlineStack>
                  </Box>
                  <Divider />
                </>
              ) : trackingPages?.length ? (
                trackingPages?.map((page, index) => {
                  return (
                    <React.Fragment key={index}>
                      <Box background="bg-surface" padding={"500"}>
                        <InlineStack align="space-between" blockAlign="center" wrap={false}>
                          <InlineStack blockAlign="center" wrap={false} gap={"400"}>
                            <div className="vDAEg">
                              <img
                                // style={{ aspectRatio: "5/4", objectFit: "contain" }}
                                alt={`Page preview thumbnail for ${page?.page_name}`}
                                src={page?.theme_type === "Traditional" ? ShopifyImage : Modern}
                              />
                            </div>
                            <BlockStack>
                            <LegacyStack>
                              <LegacyStack.Item>
                                <Text as="h3" variant="headingMd">
                                  {page?.page_name}
                                </Text>
                              </LegacyStack.Item>
                              <LegacyStack.Item>
                                {page?.active_status ?
                                    <Badge tone="success">
                                  <Text as="span" variant="bodySm">
                                  Default
                                  </Text>
                                  </Badge>
                                    :<></>
                                }

                              </LegacyStack.Item>
                            </LegacyStack>
                              <Text as="span" variant="bodyMd" tone="subdued">
                                {
                                  page?.theme_type === "Traditional"?
                                      `https://${shop}/pages/${page?.page_handle}`
                                      :
                                      `https://${shop}/a/track/order`
                                }
                              </Text>
                              <Text as="span" variant="bodyMd" tone="subdued">
                                Last saved: {formatDate(page?.updated_at)}
                              </Text>
                              {/*<Text as="span" variant="bodyMd" tone="subdued">*/}
                              {/*  Template: {page?.theme_type === "Traditional" ? "Traditional" : "Modern"}*/}
                              {/*</Text>*/}

                            </BlockStack>
                          </InlineStack>
                          <ButtonGroup>

                            {
                              page?.active_status
                                ?<></>
                                  :
                                  <Button loading={btnLoading[page?.id]} onClick={() => handlePublishPage(page?.id)} size="medium" textAlign="center">
                                    <Text as="span" variant="bodySm" fontWeight="medium">
                                      Set as default
                                    </Text>
                                  </Button>
                            }

                            <Button
                              target={page?.theme_type === "Traditional" ? "_blank" : ""}
                              url={
                                page?.theme_type === "Traditional"
                                  ? `https://${shop}/admin/themes/${themeId}/editor?previewPath=/pages/${page?.page_handle}`
                                  :
                                    `/tracking-page/customization/${page?.id}`
                              }
                              size="medium"
                              textAlign="center"
                              variant={
                                  page?.active_status
                                  ?"primary"
                                  :"secondary"
                              }
                            >
                              <Text as="span" variant="bodySm" fontWeight="medium">
                                Customize
                              </Text>
                            </Button>
                            <Popover
                                active={popovers[page?.id]}
                                activator={
                                  <Button
                                      onClick={() => togglePopoverActive(page?.id)}
                                      size="medium"
                                      textAlign="center"
                                      icon={
                                        <span className="Polaris-Icon">
                                      <svg viewBox="0 0 20 20" className="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                                        <path d="M6 10a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"></path>
                                        <path d="M11.5 10a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"></path>
                                        <path d="M17 10a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"></path>
                                      </svg>
                                    </span>
                                      }
                                  ></Button>
                                }
                                autofocusTarget="first-node"
                                onClose={() => togglePopoverActive(page?.id)}
                            >
                              <ActionList
                                  actionRole="menuitem"
                                  items={[
                                    { content: "Preview", onAction: () =>{
                                            page?.theme_type === "Traditional"?
                                                window.open(`https://${shop}/pages/${page?.page_handle}`, "_blank")
                                                :
                                                window.open(`https://${shop}/a/track/order`, "_blank")
                                        }
                                             },
                                    page?.theme_type === "Traditional"
                                        ? {
                                          content: "Page edit",
                                          url: `https://${shop}/admin/pages/${page?.shopify_page_id}`,
                                          target: "_blank",
                                        }
                                        : "",
                                    {
                                      content: "Duplicate",
                                      onAction: () => handleDuplicateModal(page?.id),
                                    },
                                    page?.active_status==0?
                                    {
                                      content: "Remove",
                                      destructive: true,
                                      onAction: () => handleDeletePageModal(page?.id),
                                    }:""
                                  ]}
                              />
                            </Popover>
                          </ButtonGroup>
                        </InlineStack>
                      </Box>
                      <Divider />
                    </React.Fragment>
                  );
                })
              ) : (
                  <>
                <Box padding={"600"}>
                  <BlockStack inlineAlign="center">
                    <Box maxWidth="60%">
                      <BlockStack inlineAlign="center">
                        <BlockStack gap={300}>
                          <div className="flex justify-center items-center">
                            {" "}
                            <img src={NoTrackingPage} width={48} height={48} alt="" />
                          </div>
                          <Text as="p" variant="headingLg" alignment="center">
                            No Tracking Pages Found
                          </Text>
                          <Text as="p" variant="bodyMd" tone="subdued" alignment="center">
                            There is no tracking pages. Please add pages to see them here.{" "}
                          </Text>
                        </BlockStack>
                      </BlockStack>
                    </Box>
                  </BlockStack>
                </Box>
                    <Divider />
                </>
              )}
              <Box padding={"400"}>
                {loading ? (<><SkeletonThumbnail size="extraSmall" /></>):(
              <Button onClick={() => handleChangeDNDModal()} >Add</Button>
                    )}
              </Box>
            </Card>
          </Layout.Section>
        <Layout.Section>
            <Card>
              <BlockStack gap="200">

                <InlineStack gap="100" align={"space-between"}>
                  <Text variant="headingMd" as="h3">
                    Translation
                  </Text>
                  {loading ? (<><SkeletonThumbnail size="extraSmall" /></>):(
                      <div>
                        {editTranslationModal?
                            <Button  variant="plain" onClick={() => handleEditTranslationModal()} >Close</Button>

                            :
                            <Button  variant="plain" onClick={() => handleEditTranslationModal()} >Edit</Button>

                        }
                      </div>
                  )}
                </InlineStack>
                {loading ? (
                        <>
                            <Box background="bg-surface" padding={"500"}>
                              <InlineStack align="space-between" blockAlign="center" gap={"400"} wrap={false}>
                                <SkeletonThumbnail size="large" />
                                <SkeletonBodyText />
                              </InlineStack>
                            </Box>
                        </>
                    ) :
                    <>
                      {editTranslationModal?
                          <FormLayout>

                            <BlockStack gap={300}>
                              <InlineGrid columns="2" gap={300}>
                                <Select
                                    label={"Published languages"}
                                    options={languages}
                                    onChange={(value) => handleChangeLang(value)}
                                    value={defaultLanguage}
                                />
                                <></>
                              </InlineGrid>
                              <Text variant={"headingMd"}>Order lookup section</Text>
                              <InlineGrid columns="2" gap={300}>
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
                              </InlineGrid>

                              <Text variant={"headingMd"}>Shipping Progress Bar</Text>
                              <InlineGrid columns="2" gap={300}>
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
                              </InlineGrid>
                              <InlineStack gap="100" align={"space-between"}>
                                <div></div>
                                <Button loading={btnLoading}  variant="primary" onClick={() => handleSubmit()} >Update</Button>

                              </InlineStack>
                            </BlockStack>
                          </FormLayout>
                          :
                          <>
                          <Box borderColor="border" borderWidth="025" borderRadius="200" padding="200">
                            <InlineStack blockAlign="start" wrap={true} align="start" gap={200}>
                              <div>
                                <Icon
                                    source={LanguageTranslateIcon}
                                    tone="base"
                                />
                              </div>

                              <Text variant="bodyMd" as="p" >
                                {getSelectedLabel(defaultLanguage)}
                              </Text>
                            </InlineStack>
                          </Box>
                            <Banner tone="info">
                              <p>You can edit the default translation text. The tracking page's language will automatically change when customers switch languages in your store</p>
                            </Banner>
                            </>
                      }
                    </>

                }

              </BlockStack>

            </Card>
        </Layout.Section>
          {/*<Layout.Section>
            <Card>
              <Box paddingBlockEnd={"400"}>
                <LegacyStack>
                  <LegacyStack.Item>
                    <Box background="bg-fill-info-secondary" borderRadius="200" padding={"300"}>
                      <Icon tone="info" source={ThemeIcon} />
                    </Box>
                  </LegacyStack.Item>
                  <LegacyStack.Item fill>
                    <Text as="h2" variant="headingMd">
                      Page themes
                    </Text>
                    <Text>Choose what type of tracking page you want</Text>
                  </LegacyStack.Item>
                </LegacyStack>
              </Box>
              <div className="nYb5p">
                <div className="hT2vd">
                  <Card padding={"0"}>
                    <div className="QOdNm">
                      <Link target="_blank">
                        <img src="https://cdn.shopify.com/theme-store/zx2c5zmpirbt8e0j4p4smtb1oa2d.jpg" alt="Screenshot of the Dawn theme"></img>
                      </Link>
                    </div>
                    <div className="wzpgr">
                      <Box padding={"400"}>
                        <InlineStack align="space-between" wrap={false} blockAlign="center">
                          <Box>
                            <Text fontWeight="semibold">Traditional</Text>
                            <Text as="span" variant="bodyMd" tone="subdued">
                              Online Store Editor with effortless customization
                            </Text>
                          </Box>
                          <Button onClick={() => handleChangeDNDModal("Traditional")} size="medium" textAlign="center">
                            <Text as="span" variant="bodySm" fontWeight="medium">
                              Add
                            </Text>
                          </Button>
                        </InlineStack>
                      </Box>
                    </div>
                  </Card>
                </div>
                <div className="hT2vd">
                  <Card padding={"0"}>
                    <div className="QOdNm">
                      <Link target="_blank">
                        <img src="https://cdn.shopify.com/theme-store/zx2c5zmpirbt8e0j4p4smtb1oa2d.jpg" alt="Screenshot of the Dawn theme"></img>
                      </Link>
                    </div>
                    <div className="wzpgr">
                      <Box padding={"400"}>
                        <InlineStack align="space-between" wrap={false} blockAlign="center">
                          <Box>
                            <Text fontWeight="semibold">Modern</Text>
                            <Text as="span" variant="bodyMd" tone="subdued">
                              Tailored for an exceptional mobile experience
                            </Text>
                          </Box>
                          <Button onClick={handleChangeModernModal} size="medium" textAlign="center">
                            <Text as="span" variant="bodySm" fontWeight="medium">
                              Add
                            </Text>
                          </Button>
                        </InlineStack>
                      </Box>
                    </div>
                  </Card>
                </div>
              </div>
            </Card>
          </Layout.Section>*/}
          <Layout.Section></Layout.Section>
          <Layout.Section></Layout.Section>
        </Layout>
        </Box>
        {toastErrorMsg}
        {toastSuccessMsg}
        <Modal
            // size="large"
            open={activeDNDModal}
            onClose={handleChangeDNDModalClose}
            title="Open Store Tracking Page Creator"
            primaryAction={{
              loading: btnLoading["Add page"],
              disabled: btnLoading["Add page"],
              content: dndCurrentStep === 3 ? "Add page" : dndCurrentStep === 4 ? "Customize" : "Next",
              onAction:
                  dndCurrentStep === 3
                      ? () => handleAddPage("Add page")
                      : dndCurrentStep === 4
                          ? () => window.open(`https://${shop}/admin/themes/${themeId}/editor?previewPath=/pages/${dndData?.page_URL_handle}`, "_blank")
                          : dndGoToNextStep,
            }}
            /* secondaryActions={
                 dndCurrentStep !== 4 && [
                   {
                     // disabled: btnLoading["Add page"],
                     content: "Previous",
                     disabled: dndCurrentStep === 3,
                     onAction: dndGoToPreviousStep,
                   },
                 ]
             }*/
        >
          <Modal.Section>
            {/*<Scrollable  style={{height: '300px'}} >*/}
            {/*<Box >*/}
            <BlockStack gap={"400"}>
              {dndCurrentStep === 1 ? (
                  DNDStepOne(handleDndStyle)
              ) : dndCurrentStep === 2 ? (
                  DNDStepTwo(handleDndLayout)
              ) : dndCurrentStep === 3 ? (
                  <>
                    <Text fontWeight="semibold"> Pick tracking page url</Text>
                    <Banner tone="warning">
                      <p>If a page with an identical URL exists, we will replace it with</p>
                    </Banner>
                    <TextField
                        label="Page title (only English)"
                        value={dndData?.page_title}
                        onChange={(value) => handleChangeDndData("page_title", value)}
                        autoComplete="off"
                        error={dndTitleError ? "Page title is required" : ""}
                    />

                    {
                      themeType === 'Modern'?
                          <TextField disabled label="URL and handle" prefix={`https://${shop}/a/track/order`} autoComplete="off" />
                          :
                          <TextField
                              label="URL and handle"
                              value={dndData?.page_URL_handle}
                              prefix={`https://${shop}/pages/`}
                              // onChange={(value) => handleChangeDndData("page_URL_handle", value)}
                              autoComplete="off" disabled
                          />
                    }

                    <Text fontWeight={"regular"}> Theme</Text>
                    <div className="customRadio">
                      <InlineGrid gap="100" columns={2}>
                        <RadioButton
                            label={
                              <>
                                <div className="hT2vd"  style={{
                                  width: "100%",
                                  height: "auto",
                                  borderRadius:"5px",
                                  border: themeType === 'Traditional' ? '3px solid black' : '3px solid #cccccc'
                                }}>

                                  <div className="QOdNm">
                                    <img src={ShopifyImage} alt="Traditional Tracking"></img>
                                  </div>
                                  <div className="wzpgr">
                                    <Box padding={"400"}>
                                      <InlineStack align="space-between" wrap={false} blockAlign="center">
                                        <Box>
                                          <Text fontWeight="semibold">Traditional</Text>
                                          <Text as="span" variant="bodyMd" tone="subdued">
                                            Online Store Editor with effortless customization
                                          </Text>
                                        </Box>
                                      </InlineStack>
                                    </Box>
                                  </div>
                                </div>
                              </>}
                            checked={themeType === 'Traditional'}
                            id="Traditional"
                            name="map_theme"
                            onChange={() => handleThemeType(true, 'Traditional')}
                            value={themeType}
                            // helpText="Default"
                        />
                        <RadioButton
                            label={
                              <>
                                <div className="hT2vd" style={{
                                  width: "100%",
                                  height: "auto",
                                  borderRadius:"5px",
                                  border: themeType === 'Modern' ? '3px solid black' : '3px solid #cccccc'
                                }}>
                                  <div className="QOdNm">
                                    <img src={Modern} alt="Modren Tracking"></img>
                                  </div>
                                  <div className="wzpgr">
                                    <Box padding={"400"}>
                                      <InlineStack align="space-between" wrap={false} blockAlign="center">
                                        <Box>
                                          <Text fontWeight="semibold">Modern</Text>
                                          <Text as="span" variant="bodyMd" tone="subdued">
                                            Tailored for an exceptional mobile experience
                                          </Text>
                                        </Box>
                                      </InlineStack>
                                    </Box>
                                  </div>
                                </div>
                              </>}
                            checked={themeType === 'Modern'}
                            id="default"
                            name="Modern"
                            onChange={() => handleThemeType(true, 'Modern')}
                            value={themeType}
                            // helpText="Default"
                        />

                      </InlineGrid>
                    </div>
                  </>
              ) : (
                  <Text>
                    Your tracking page URL:{" "}
                    {
                      themeType === 'Modern'?
                          <Link
                              url={`https://${shop}/a/track/order`}
                              target="_blank"
                          >{`https://${shop}/a/track/order`}</Link>
                          :
                          <Link
                              url={`https://${shop}/pages/${dndData?.page_URL_handle}`}
                              target="_blank"
                          >{`https://${shop}/pages/${dndData?.page_URL_handle}`}</Link>
                    }
                  </Text>
              )}
            </BlockStack>
            {/*</Box>*/}
            {/*</Scrollable>*/}
          </Modal.Section>
        </Modal>
        <Modal
            size="large"
            open={activeModernModal}
            onClose={handleChangeModernModal}
            title="Modern tracking page creator"
            primaryAction={{
              content: modernCurrentStep === MODERN_NUMBER_OF_STEPS ? "Add page " : "Next",
              onAction: modernCurrentStep === MODERN_NUMBER_OF_STEPS ? addPage : modernGoToNextStep,
            }}
            secondaryActions={[
              {
                content: "Previous",
                disabled: modernCurrentStep === 1,
                onAction: modernGoToPreviousStep,
              },
            ]}
        >
          <Modal.Section>
            <Box padding={"400"}>
              <BlockStack gap={"400"}>
                {modernCurrentStep === 1
                    ? ModernStepOne(handleModernStyle)
                    : ModernStepThree(shop, modernData, modernTitleError, handleChangeModernData)}
              </BlockStack>
            </Box>
          </Modal.Section>
        </Modal>

        <Modal
            open={activeDuplicateModal}
            onClose={handleDuplicateModalClose}
            title="Page title"
            primaryAction={{
              content: "Duplicate",
              loading: btnLoading["Duplicate"],
              onAction: () => handleDuplicatePage("Duplicate"),
            }}
        >
          <Modal.Section>
            <TextField
                label="Page title (only English)"
                labelHidden
                value={duplicatePageTitle}
                onChange={(value) => setDuplicatePageTitle(value)}
                autoComplete="off"
                error={duplicatePageTitleError ? "Title is required" : ""}
            />
          </Modal.Section>
        </Modal>

        <Modal
            open={activeDeleteModal}
            onClose={handleDeletePageModalClose}
            title="Remove page"
            primaryAction={{
              content: "Remove",
              destructive: true,
              loading: btnLoading["Remove"],
              onAction: () => handleDeletePage("Remove"),
            }}
            secondaryActions={{
              content: "Cancel",
              onAction: handleDeletePageModalClose,
            }}
        >
          <Modal.Section>
            <Text>Are you sure you want to remove this page?</Text>
          </Modal.Section>
        </Modal>
      </Page>
    </Frame>
  );
}

