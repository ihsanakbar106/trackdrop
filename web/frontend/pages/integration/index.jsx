import { useAppBridge, useNavigate } from "@shopify/app-bridge-react";
import {
  BlockStack,
  Button,
  Card,
  Connected,
  Grid,
  InlineStack,
  Layout,
  Link,
  Modal,
  Page,
  Text,
  TextField,
  Thumbnail,
  Toast,
} from "@shopify/polaris";
import React, { useCallback, useContext, useEffect, useState } from "react";
import { AppContext } from "../../components";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import klaviyo from "../../assets/klaviyo.webp";
import ShopifyFlow from "../../assets/ShopifyFlow.webp";

export default function Integration() {
  const appBridge = useAppBridge();
  const { apiUrl } = useContext(AppContext);
  const [loading, setLoading] = useState(true);
  const [btnLoading, setBtnLoading] = useState(false);
  const [errorToast, setErrorToast] = useState(false);
  const [successToast, setSuccessToast] = useState(false);
  const [toastMsg, setToastMsg] = useState("");
  const [toggleData, setToggleData] = useState(true);
  const [activeModal, setActiveModal] = useState(null);
  const [formData, setFormData] = useState({});
  const [fieldErrors, setFieldErrors] = useState({}); // Track field errors
  const [generalSettings, setGeneralSettings] = useState(null);
  const integrations = [
    {
      id: "klaviyo",
      name: "Klaviyo",
      type: "Email Marketing & SMS",
      description: "Grow faster and more efficiently with email, sms, reviews and more. Powered by your customer data.",
      icon: klaviyo,
      connected: generalSettings?.klaviyo_api_keys ? true : false,
      modalDescription: "You can find your Private API Key in Klaviyo, go to Account > Settings > Account > API Keys, copy the Public API Key",
      fields: [{ name: "klaviyoId", label: "Private API Key", type: "text" }],
    },
    {
      id: "ShopifyFlow",
      name: "Shopify Flow",
      type: "Automate Tasks",
      description: "Shopify Flow empowers you to build custom automations that help you run your business more efficiently.",
      icon: ShopifyFlow,
    },
  ];

  const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
  const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

  const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

  const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(`${apiUrl}general-settings`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      const { setting } = response?.data;
      setGeneralSettings(setting || null);
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

  const handleConnect = (integrationId) => {
    setActiveModal(integrationId);
    setFieldErrors({}); // Clear field errors when opening a modal
  };

  const handleCloseModal = () => {
    setActiveModal(null);
    setFieldErrors({}); // Clear field errors when closing a modal
  };

  const handleInputChange = (fieldName, value) => {
    setFormData((prevData) => ({
      ...prevData,
      [fieldName]: value,
    }));
    setFieldErrors((prevErrors) => ({
      ...prevErrors,
      [fieldName]: "", // Clear error when the user types
    }));
  };

  const handleConfirmConnect = async (integrationId) => {
    // Validate the required fields
    const requiredFields = integrations.find((integration) => integration.id === integrationId)?.fields || [];
    const newFieldErrors = {};

    requiredFields.forEach((field) => {
      if (!formData[field.name]) {
        newFieldErrors[field.name] = `${field.label} is required`;
      }
    });

    if (Object.keys(newFieldErrors).length > 0) {
      setFieldErrors(newFieldErrors);
      return;
    }

    setBtnLoading((prev) => {
      let toggleId;
      if (prev[integrationId]) {
        toggleId = { [integrationId]: false };
      } else {
        toggleId = { [integrationId]: true };
      }
      return { ...toggleId };
    });
    try {
      let sessionToken = await getSessionToken(appBridge);
      const payload = {
        klaviyo_api_keys: formData.klaviyoId,
      };

      const response = await axios.post(`${apiUrl}klaviyo-api-save`, payload, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });
      if (response?.data?.status == "success") {
        setBtnLoading(false);
        setSuccessToast(true);
        setToastMsg(response?.data?.message);
        setActiveModal(null);
        setFormData({});
      }
      if (response?.data?.status == "error") {
        setBtnLoading(false);
        setErrorToast(true);
        setToastMsg(response?.data?.message);
      }
    } catch (error) {
      setBtnLoading(false);
    }
  };

  return (
    <Page title="Integrations">
      <Layout>
        <Layout.Section>
          <Grid gap={{ xs: "1rem", sm: "1rem", md: "1rem", lg: "1rem", xl: "1rem" }}>
            {integrations.map((integration) => (
              <IntegrationCard key={integration.id} integration={integration} onConnect={handleConnect} />
            ))}
          </Grid>
        </Layout.Section>
        {integrations.map((integration) => (
          <IntegrationModal
            key={integration.id}
            integration={integration}
            active={activeModal === integration.id}
            onClose={handleCloseModal}
            onConnect={handleConfirmConnect}
            formData={formData}
            onInputChange={handleInputChange}
            fieldErrors={fieldErrors} // Pass field errors to the modal
            btnLoading={btnLoading}
          />
        ))}
      </Layout>
      {toastSuccessMsg}
      {toastErrorMsg}
    </Page>
  );
}

function IntegrationCard({ integration, onConnect }) {
  return (
    <Grid.Cell columnSpan={{ xs: 12, sm: 6, md: 6, lg: 6, xl: 4 }}>
      <Card>
        <BlockStack gap={"400"}>
          <BlockStack gap={"200"}>
            <InlineStack gap="300">
              <div className="AppItem">
                <Thumbnail source={integration.icon} />
              </div>
              <BlockStack align="center" gap="100">
                <Text variant="headingMd" as="h6">
                  {integration.name}
                </Text>
                <Text variant="bodySm" as="p">
                  {integration.type}
                </Text>
              </BlockStack>
            </InlineStack>
            <Text variant="bodyMd" as="p">
              {integration.description}
            </Text>
          </BlockStack>
          {integration?.id === "ShopifyFlow" ? (
            <BlockStack gap="200">
              <InlineStack gap={"200"}>
                <Button onClick={() => onConnect(integration.id)}>Install app</Button>
                <Link monochrome removeUnderline>
                  Learn more
                </Link>
              </InlineStack>
            </BlockStack>
          ) : (
            <InlineStack>
              {integration?.connected ? (
                <Button disabled>Connected</Button>
              ) : (
                <Button variant="primary" onClick={() => onConnect(integration.id)}>
                  Connect
                </Button>
              )}
            </InlineStack>
          )}
        </BlockStack>
      </Card>
    </Grid.Cell>
  );
}

function IntegrationModal({ integration, active, onClose, onConnect, formData, onInputChange, fieldErrors, btnLoading }) {
  const handleSubmit = () => {
    onConnect(integration.id);
  };
  return (
    <Modal
      open={active}
      onClose={onClose}
      title={`${integration.name} Integration`}
      primaryAction={{
        content: "Connect",
        loading: btnLoading[integration?.id],
        onAction: handleSubmit,
      }}
      secondaryActions={[
        {
          content: "Cancel",
          onAction: onClose,
        },
      ]}
    >
      <Modal.Section>
        <BlockStack gap="400">
          <p>{integration.modalDescription}</p>
          {integration?.fields?.map((field) => (
            <TextField
              key={field.name}
              label={field.label}
              type={field.type}
              value={formData[field?.name] || ""}
              onChange={(value) => onInputChange(field.name, value)}
              error={fieldErrors[field.name]} // Display error under the text field
            />
          ))}
          <BlockStack gap="100">
            {integration?.events?.map((event) => (
              <InlineStack gap="200">
                <Text variant="bodyMd" as="h6" fontWeight="semibold">
                  {event.name}
                </Text>
                <Text variant="bodyMd" as="p">
                  {event.description}
                </Text>
              </InlineStack>
            ))}
          </BlockStack>
        </BlockStack>
      </Modal.Section>
    </Modal>
  );
}
