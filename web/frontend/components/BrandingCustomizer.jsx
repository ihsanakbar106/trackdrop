import {
  BlockStack,
  Box,
  Button,
  ChoiceList,
  ColorPicker,
  Divider,
  DropZone,
  InlineStack,
  LegacyStack,
  Popover,
  RangeSlider,
  Select,
  Text,
  TextField,
  Thumbnail,
} from "@shopify/polaris";
import { ChevronLeftIcon } from "@shopify/polaris-icons";
import React, { useCallback, useState } from "react";

export default function BrandingCustomizer({
  setting,
  handleSetting,
  customizedata,
  handleChangeValue,
  colorPicker,
  colorPickerFunc,
  handleDropZoneDrop,
  handleRemoveLogo,
}) {
  const [popoverActiveTextColor, setPopoverActiveTextColor] = useState(false);
  const [popoverActiveBackgroundColor, setPopoverActiveBackgroundColor] = useState(false);
  const validImageTypes = ["image/gif", "image/jpeg", "image/png"];

  const togglePopoverActiveTextColor = useCallback(() => setPopoverActiveTextColor((popoverActiveTextColor) => !popoverActiveTextColor), []);

  const togglePopoverActiveBackgroundColor = useCallback(
    () => setPopoverActiveBackgroundColor((popoverActiveBackgroundColor) => !popoverActiveBackgroundColor),
    [],
  );

  const fileUpload = !customizedata?.logo && <DropZone.FileUpload actionTitle="Add image" actionHint="Accepts .jpg, .png, .gif, .jpeg." />;
  const uploadedFile = customizedata?.logo && (
    <LegacyStack>
      <Thumbnail
        size="small"
        alt={customizedata?.logo.name}
        source={validImageTypes.includes(customizedata?.logo.type) ? window.URL.createObjectURL(customizedata?.logo) : NoteIcon}
      />
      <div>
        {customizedata?.logo.name}
        <Text variant="bodySm" as="p">
          {customizedata?.logo.size} bytes
        </Text>
      </div>
    </LegacyStack>
  );

  return (
    <div className="h-full">
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
          <ChoiceList
            title="Branding Type"
            choices={[
              { label: "Store name", value: "Store name" },
              { label: "Image", value: "Image" },
            ]}
            selected={customizedata?.brandingType}
            onChange={(selected) => handleChangeValue("brandingType", selected)}
          />
          {customizedata?.brandingType == "Store name" ? (
            <>
              {colorField(
                "Background Color",
                "textColor",
                customizedata?.textColor,
                handleChangeValue,
                popoverActiveTextColor,
                togglePopoverActiveTextColor,
                colorPickerFunc,
                colorPicker,
              )}
            </>
          ) : (
            <>
              {customizedata?.logo ? (
                <InlineStack blockAlign="center" align="space-between">
                  <Thumbnail
                    size="large"
                    alt={customizedata?.logo.name}
                    source={validImageTypes.includes(customizedata?.logo.type) ? window.URL.createObjectURL(customizedata?.logo) : NoteIcon}
                  />
                  <Button onClick={handleRemoveLogo} variant="tertiary" size="medium" textAlign="center">
                    Remove
                  </Button>
                </InlineStack>
              ) : (
                <DropZone label="Logo" allowMultiple={false} onDrop={handleDropZoneDrop}>
                  {uploadedFile}
                  {fileUpload}
                </DropZone>
              )}

              <RangeSlider
                label="Image Width (%)"
                value={customizedata?.imageWidth}
                onChange={(value) => handleChangeValue("imageWidth", value)}
                output
                min={0}
                max={100}
              />
            </>
          )}

          <Divider />
        </BlockStack>
        {colorField(
          "Background Color",
          "brandingBackgroundColor",
          customizedata?.brandingBackgroundColor,
          handleChangeValue,
          popoverActiveBackgroundColor,
          togglePopoverActiveBackgroundColor,
          colorPickerFunc,
          colorPicker,
        )}
        <TextField
          label="Padding Top"
          value={customizedata?.paddingTop}
          onChange={(value) => handleChangeValue("paddingTop", value)}
          type="number"
          suffix="px"
          autoComplete="off"
        />
        <TextField
          label="Padding Bottom"
          value={customizedata?.paddingBottom}
          onChange={(value) => handleChangeValue("paddingBottom", value)}
          type="number"
          suffix="px"
          autoComplete="off"
        />
      </div>
    </div>
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
