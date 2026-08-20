import {
  BlockStack,
  Box,
  Button,
  ButtonGroup,
  ChoiceList,
  ColorPicker,
  Divider,
  DropZone,
  InlineStack,
  Label,
  LegacyStack,
  Popover,
  RangeSlider,
  Select,
  Text,
  TextField,
  Thumbnail,
} from "@shopify/polaris";
import {
  ChevronLeftIcon,
  LayoutLogoBlockIcon,
  LayoutSidebarLeftIcon,
  LayoutSidebarRightIcon,
} from "@shopify/polaris-icons";
import React, { useCallback, useState } from "react";

export default function TrackingButton({
  setting,
  handleSetting,
  customizedata,
  handleChangeValue,
  colorPicker,
  colorPickerFunc
}) {
  const [popoverActiveButtonColor, setPopoverActiveButtonColor] = useState(false);
  const [popoverActiveTextColor, setPopoverActiveTextColor] = useState(false);
  const [popoverActiveBackgroundColor, setPopoverActiveBackgroundColor] = useState(false);
  const validImageTypes = ["image/gif", "image/jpeg", "image/png"];

  const togglePopover = (popoverStateFunction) => () => popoverStateFunction((active) => !active);

  const togglePopoverActiveTextColor = useCallback(
    () => setPopoverActiveTextColor((popoverActiveTextColor) => !popoverActiveTextColor),
    [],
  );

  const togglePopoverActiveBackgroundColor = useCallback(
    () =>
      setPopoverActiveBackgroundColor(
        (popoverActiveBackgroundColor) => !popoverActiveBackgroundColor,
      ),
    [],
  );

  const fileUpload = !customizedata?.logo && (
    <DropZone.FileUpload actionTitle="Add image" actionHint="Accepts .jpg, .png, .gif, .jpeg." />
  );
  const uploadedFile = customizedata?.logo && (
    <LegacyStack>
      <Thumbnail
        size="small"
        alt={customizedata?.logo.name}
        source={
          validImageTypes.includes(customizedata?.logo.type)
            ? window.URL.createObjectURL(customizedata?.logo)
            : NoteIcon
        }
      />
      <div>
        {customizedata?.logo.name}
        <Text variant="bodySm" as="p">
          {customizedata?.logo.size} bytes
        </Text>
      </div>
    </LegacyStack>
  );

  const renderChildren = useCallback(
    (isSelected) =>
      isSelected && (
        <TextField
          label="Minimum Quantity"
          labelHidden
          onChange={(value) => handleChangeValue("trackingButtonCustomizeLink", value)}
          value={customizedata?.trackingButtonCustomizeLink}
          autoComplete="off"
        />
      ),
    [handleChangeValue, customizedata?.trackingButtonCustomizeLink],
  );

  return (
    <div className="h-full">
      <Box padding={"400"}>
        <InlineStack blockAlign="center" align="start" gap={"300"}>
          <Button
            onClick={() => handleSetting("Section")}
            size="medium"
            variant="tertiary"
            textAlign="center"
            icon={ChevronLeftIcon}
          />
          <Text as="h1" variant="headingMd">
            {setting}
          </Text>
        </InlineStack>
      </Box>
      <Divider />
      <div
        className="flex flex-col gap-4 p-4 pb-16 overflow-y-auto scroll_custom"
        style={{ maxHeight: "calc(-116px + 100vh)" }}
      >
        <BlockStack gap={"300"}>
          <TextField
            label="Button Text"
            value={customizedata?.trackingButtonButtonText}
            onChange={(value) => handleChangeValue("trackingButtonButtonText", value)}
            autoComplete="off"
          />
          <ChoiceList
            title="Link"
            choices={[
              { label: "Tracking Link", value: "Tracking Link" },
              { label: "Customize link", value: "Customize link", renderChildren },
            ]}
            selected={customizedata?.trackingButtonLink}
            onChange={(selected) => handleChangeValue("trackingButtonLink", selected)}
          />
          <RangeSlider
            label="Button Width (%)"
            value={customizedata?.trackingButtonButtonWidth}
            onChange={(value) => handleChangeValue("trackingButtonButtonWidth", value)}
            output
            min={0}
            max={100}
          />
          <TextField
            label="Font Size"
            value={customizedata?.trackingButtonFontSize}
            onChange={(value) => handleChangeValue("trackingButtonFontSize", value)}
            type="number"
            suffix="px"
            autoComplete="off"
          />
          {colorField(
            "Button Color",
            "trackingButtonButtonColor",
            customizedata?.trackingButtonButtonColor,
            handleChangeValue,
            popoverActiveButtonColor,
            togglePopover(setPopoverActiveButtonColor),
            colorPickerFunc,
            colorPicker,
          )}
          {colorField(
            "Text Color",
            "trackingButtonTextColor",
            customizedata?.trackingButtonTextColor,
            handleChangeValue,
            popoverActiveTextColor,
            togglePopover(setPopoverActiveTextColor),
            colorPickerFunc,
            colorPicker,
          )}
          <Divider />
          <InlineStack blockAlign="center" align="space-between">
            <Label>Alignment</Label>
            <ButtonGroup variant="segmented">
              <Button
                size="medium"
                textAlign="center"
                onClick={() => handleChangeValue("trackingButtonAlignment", "Left")}
                pressed={customizedata?.trackingButtonAlignment == "Left"}
                icon={LayoutSidebarLeftIcon}
              />
              <Button
                size="medium"
                textAlign="center"
                onClick={() => handleChangeValue("trackingButtonAlignment", "Center")}
                pressed={customizedata?.trackingButtonAlignment == "Center"}
                icon={LayoutLogoBlockIcon}
              />
              <Button
                size="medium"
                textAlign="center"
                onClick={() => handleChangeValue("trackingButtonAlignment", "Right")}
                pressed={customizedata?.trackingButtonAlignment == "Right"}
                icon={LayoutSidebarRightIcon}
              />
            </ButtonGroup>
          </InlineStack>
          {colorField(
            "Background Color",
            "trackingButtonBackgroundColor",
            customizedata?.trackingButtonBackgroundColor,
            handleChangeValue,
            popoverActiveBackgroundColor,
            togglePopover(setPopoverActiveBackgroundColor),
            colorPickerFunc,
            colorPicker,
          )}
        </BlockStack>
      </div>
    </div>
  );
}

const colorField = (
  label,
  key,
  value,
  handleChangeValue,
  popoverActiveTextColor,
  togglePopoverActiveTextColor,
  colorPickerFunc,
  colorPicker,
) => {
  return (
    <TextField
      label={label}
      value={value}
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
          <ColorPicker
            onChange={(newColor) => colorPickerFunc(newColor, key)}
            color={colorPicker}
          />
        </Popover>
      }
    />
  );
};
