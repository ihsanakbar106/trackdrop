import {
  BlockStack,
  Card,
  Icon,
  InlineStack,
  Layout,
  ResourceItem,
  ResourceList,
  SkeletonBodyText,
  Text,
} from "@shopify/polaris";
import { ChevronRightIcon } from "@shopify/polaris-icons";
import React from "react";
import { useNavigate } from "react-router-dom";

export default function EmailNotification({
  loading,
  emailsList,
  convertNumberToBoolean,
  handleCheckboxChangeIsActive,
}) {
  const navigate = useNavigate();
  return (
    <>
      <Layout.AnnotatedSection
        id="nativeShopifyNotification "
        title="Free"
        description={
          loading ? <SkeletonBodyText /> : "You can configure any add-ons listed in this section"
        }
      >
        <BlockStack gap={"400"}>
          <Card>
            <BlockStack gap={"300"}>
              <Text variant="headingSm" as="h6">
                Shipping Events to Shopify
              </Text>
              {loading ? (
                <SkeletonBodyText lines={5} />
              ) : (
                <Text variant="bodyMd" tone="subdued">
                  Send shipping updates to your Shopify store, enabling you to send native Shopify
                  native notifications, view tracking status on the order details page, and initiate
                  review app notifications based on delivery events.
                </Text>
              )}
            </BlockStack>
          </Card>
          <Card>
            <BlockStack gap={"300"}>
              <Text variant="headingSm" as="h6">
                Enhanced Email Design with OrderlyEmails
              </Text>
              {loading ? (
                <SkeletonBodyText lines={5} />
              ) : (
                <Text variant="bodyMd" tone="subdued">
                  Elevate your Shopify emails to a new level of professionalism using OrderlyEmails.
                  Seamlessly integrate and ensure every tracking link redirects to your
                  custom-branded Rush tracking page effortlessly.
                </Text>
              )}
            </BlockStack>
          </Card>
          <Card>
            <BlockStack gap={"300"}>
              <Text variant="headingSm" as="h6">
                Enhanced Email Design with OrderlyEmails
              </Text>
              {loading ? (
                <SkeletonBodyText lines={5} />
              ) : (
                <Text variant="bodyMd" tone="subdued">
                  Elevate your Shopify emails to a new level of professionalism using OrderlyEmails.
                  Seamlessly integrate and ensure every tracking link redirects to your
                  custom-branded Rush tracking page effortlessly.
                </Text>
              )}
            </BlockStack>
          </Card>
        </BlockStack>
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
              <SkeletonBodyText lines={15} />
            </Card>
          ) : (
            <Card padding={0}>
              <ResourceList
                resourceName={{ singular: "customer", plural: "customers" }}
                items={emailsList}
                renderItem={(item, index) => {
                  const { id, title, active_status } = item;

                  return (
                    <ResourceItem
                      onClick={() => navigate(`/notifications/template/${id}`)}
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
      </Layout.AnnotatedSection>
    </>
  );
}
