import {
  SkeletonPage,
  Layout,
  LegacyCard,
  SkeletonBodyText,
  TextContainer,
  SkeletonDisplayText,
  Card,
  Box,
  InlineStack,
  SkeletonTabs,
  SkeletonThumbnail,
  LegacyStack,
} from "@shopify/polaris";
import React from "react";

export default function TableSkeletonWithTabs({
  primaryAction,
  length,
  fullWidth,
  SkeletonTabsLeft,
  thumbnail,
  checkbox,
  SkeletonTabsLeftLength,
}) {
  return (
    <SkeletonPage fullWidth={fullWidth} primaryAction={primaryAction}>
      <Layout>
        <Layout.Section>
          <Card padding={"0"}>
            <Box
              padding={"200"}
              borderBlockEndWidth="025"
              borderColor="border"
              borderStyle="solid"
            >
              <InlineStack align={SkeletonTabsLeft ? "space-between" : "end"}>
                {SkeletonTabsLeft ? (
                  <InlineStack>
                    <SkeletonTabs count={SkeletonTabsLeftLength} />
                  </InlineStack>
                ) : (
                  ""
                )}
                <InlineStack>
                  <SkeletonTabs count={1} />
                </InlineStack>
              </InlineStack>
            </Box>
            {Array.from({ length: length }, (_, index) => (
              <Box
                key={index}
                padding={"200"}
                borderBlockEndWidth="025"
                borderColor="border"
                borderStyle="solid"
              >
                <LegacyStack alignment="center">
                  {checkbox ? 
                  <LegacyStack.Item>
                    <SkeletonThumbnail size="extraSmall" />
                  </LegacyStack.Item> : ""}
                  {thumbnail ? (
                    <LegacyStack.Item>
                      <SkeletonThumbnail size="small" />
                    </LegacyStack.Item>
                  ) : (
                    ""
                  )}
                  <LegacyStack.Item fill>
                    <SkeletonBodyText lines={1} />
                  </LegacyStack.Item>
                  <LegacyStack.Item fill>
                    <SkeletonBodyText lines={1} />
                  </LegacyStack.Item>
                  <LegacyStack.Item fill>
                    <SkeletonBodyText lines={1} />
                  </LegacyStack.Item>
                  <LegacyStack.Item fill>
                    <SkeletonBodyText lines={1} />
                  </LegacyStack.Item>
                  <LegacyStack.Item fill>
                    <SkeletonBodyText lines={1} />
                  </LegacyStack.Item>
                </LegacyStack>
              </Box>
            ))}
          </Card>
        </Layout.Section>
      </Layout>
    </SkeletonPage>
  );
}
