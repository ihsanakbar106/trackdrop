import { BlockStack, Box, Button, InlineStack, Label, Select, Text, Tooltip } from "@shopify/polaris";
import React from "react";

export const FreeUpsellAutomaticProd = ({ data, handleChangeData, handleChangesOpenHiddenProductModal, handleChangesClearHiddenProduct, selectedHiddenProducts }) => {
  return (
    <BlockStack gap={"400"}>
      <Select
        label="Product source for recommendation"
        options={[
          {
            label: "First line item",
            value: "first_line_item",
          },
          {
            label: "Last line item",
            value: "last_line_item",
          },
          {
            label: "Least expensive",
            value: "least_expensive",
          },
          {
            label: "Most expensive",
            value: "most_expensive",
          },
        ]}
        onChange={(value) => handleChangeData("product_source_for_recommendation", value)}
        value={data?.product_source_for_recommendation}
      />
      <Select
        label="Product recommendation mode"
        options={[
          {
            label: "Related",
            value: "RELATED",
          },
          {
            label: "Complementary",
            value: "COMPLEMENTARY",
          },
        ]}
        onChange={(value) => handleChangeData("product_recommendation_mode", value)}
        value={data?.product_recommendation_mode}
      />
      <BlockStack gap={"100"}>
        <Label>Hidden products</Label>
        <Text tone="subdued">Prevent the following products from showing.</Text>
        <BlockStack>
          <Box
            borderColor="border"
            borderRadius="200"
            borderBlockStartWidth="025"
            borderBlockEndWidth={!selectedHiddenProducts?.length ? "025" : ""}
            borderInlineStartWidth="025"
            borderInlineEndWidth="025"
            borderEndStartRadius={!selectedHiddenProducts?.length ? "200" : ""}
            borderEndEndRadius={!selectedHiddenProducts?.length ? "200" : ""}
          >
            <Box padding={"400"}>
              <BlockStack gap={"200"}>
                <InlineStack blockAlign="center" align="space-between">
                  <InlineStack gap={"300"} blockAlign="center" wrap>
                    <InlineStack wrap>
                      <Box background="bg-fill" borderColor="border" borderStyle="solid" borderRadius="150" borderWidth="025" padding={"150"}>
                        <span class="Polaris-Icon Polaris-Icon--toneSubdued">
                          <svg viewBox="1 1 18 18" class="Polaris-Icon__Svg" focusable="false" aria-hidden="true">
                            <path d="M11.977 4.751a7.598 7.598 0 0 0-1.977-.251c-2.444 0-4.196 1.045-5.325 2.233a7.188 7.188 0 0 0-1.243 1.773c-.26.532-.432 1.076-.432 1.494 0 .418.171.962.432 1.493.172.354.4.734.687 1.116l1.074-1.074a5.388 5.388 0 0 1-.414-.7c-.221-.453-.279-.753-.279-.835 0-.082.058-.382.279-.835a5.71 5.71 0 0 1 .983-1.398c.89-.937 2.264-1.767 4.238-1.767.24 0 .471.012.693.036l1.284-1.285Z"></path>
                            <path
                              fill-rule="evenodd"
                              d="M4.25 14.6a.75.75 0 0 0 1.067 1.053l1.062-1.061c.975.543 2.177.908 3.621.908 2.45 0 4.142-1.05 5.24-2.242 1.078-1.17 1.588-2.476 1.738-3.076a.749.749 0 0 0 0-.364c-.15-.6-.66-1.906-1.738-3.076a7.245 7.245 0 0 0-.51-.502l.923-.923a.75.75 0 0 0-1.053-1.068l-.008.008-10.335 10.336-.008.007Zm5.75-.6c-.978 0-1.809-.204-2.506-.523l1.108-1.109a2.75 2.75 0 0 0 3.766-3.766l1.3-1.299c.169.147.325.3.469.455a6.387 6.387 0 0 1 1.332 2.242 6.387 6.387 0 0 1-1.332 2.242c-.86.933-2.17 1.758-4.137 1.758Zm0-2.75c-.087 0-.172-.01-.254-.026l1.478-1.478a1.25 1.25 0 0 1-1.224 1.504Z"
                            ></path>
                          </svg>
                        </span>
                      </Box>
                    </InlineStack>
                    <Tooltip content="Selected products are hidden across all blocks." hasUnderline>
                      <Text>{`${selectedHiddenProducts?.length} Products`}</Text>
                    </Tooltip>
                  </InlineStack>
                  <Button size="medium" textAlign="center" onClick={handleChangesOpenHiddenProductModal}>
                    {selectedHiddenProducts?.length ? "Edit products" : "Add products"}
                  </Button>
                </InlineStack>
              </BlockStack>
            </Box>
          </Box>
          {selectedHiddenProducts?.length ? (
            <Box background="bg-surface-secondary" padding={"200"} borderColor="border" borderRadius="200" borderWidth="025" borderStartStartRadius="0" borderStartEndRadius="0">
              <InlineStack align="end">
                <Button onClick={handleChangesClearHiddenProduct} variant="tertiary" size="medium" textAlign="center" tone="critical">
                  <Text variant="bodySm" fontWeight="medium">
                    Clear selection
                  </Text>
                </Button>
              </InlineStack>
            </Box>
          ) : (
            ""
          )}
        </BlockStack>
      </BlockStack>
    </BlockStack>
  );
};
