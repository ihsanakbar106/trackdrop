import {
  BlockStack,
  Card,
  Icon,
  InlineStack,
  Layout,
  LegacyCard,
  ResourceItem,
  ResourceList,
  Tabs,
  Text,
  TextField,
} from "@shopify/polaris";
import { ChevronRightIcon } from "@shopify/polaris-icons";
import React, { useCallback, useState } from "react";

export default function WebBaseNotification({
  webNotificationList,
  convertNumberToBoolean,
  handleCheckboxChangeIsActive,
}) {
  const [selected, setSelected] = useState(0);

  console.log("selected");

  const handleTabChange = useCallback((selectedTabIndex) => {
    console.log("selectedTabIndex", selectedTabIndex);
    setSelected(selectedTabIndex);
  }, []);

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

  return (
    // <Layout.Section variant="fullWidth">
    //   <Card>
    //     <Layout>
    //       <Layout.Section variant="oneThird">
    //         <BlockStack gap={"300"}>
    //           <Text variant="headingSm" as="h6">
    //             Basic Details
    //           </Text>
    //           <TextField label="Title" autoComplete="off" />
    //           <TextField label="Message" autoComplete="off" />
    //         </BlockStack>
    //       </Layout.Section>
    //       <Layout.Section variant="oneThird">
    //         <Tabs tabs={tabs} selected={selected} onSelect={handleTabChange} fitted>
    //           <LegacyCard.Section title={tabs[selected].content}>
    //             <p>Tab {selected} selected</p>
    //           </LegacyCard.Section>
    //         </Tabs>
    //       </Layout.Section>
    //     </Layout>
    //   </Card>
    // </Layout.Section>
    <Layout.AnnotatedSection
      id="notifications"
      title="Notifications"
      description="Notifications are automatically sent to customers when conditions are met, custom rules will take priority."
    >
      <Card>
        <Card padding={0}>
          <ResourceList
            resourceName={{ singular: "customer", plural: "customers" }}
            items={webNotificationList}
            renderItem={(item, index) => {
              const { id, title, active_status } = item;

              return (
                <ResourceItem id={id} accessibilityLabel={`View details for ${title}`}>
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
      </Card>
    </Layout.AnnotatedSection>
  );
}
