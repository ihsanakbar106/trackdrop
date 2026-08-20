import {
    Button,
    ChoiceList,
    LegacyCard,
    LegacyStack,
    SkeletonThumbnail,
    TextField,
    Thumbnail,
} from "@shopify/polaris";
import React from "react";

export default function CustomHeader({
    customHeader,
    handleChangeHeader,
    handleChangeHeaderLogoMaxWidth,
    mediaImages,
    handleCustomHeaderImage,
    handleLoadMore,
    loadMoreBtnLoading,
    handleResetImagesHeader,
}) {
    const selectedBannerImage = mediaImages?.find(
        (image) => image?.id === customHeader?.header_banner_image_id
    );
    const selectedLogoImage = mediaImages?.find(
        (image) => image?.id === customHeader?.header_logo_image_id
    );
    return (
        <>
            <LegacyCard.Section>
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Alignment"
                            choices={[
                                {
                                    label: "Center",
                                    value: "CENTER",
                                },
                                {
                                    label: "End",
                                    value: "END",
                                },
                                {
                                    label: "Start",
                                    value: "START",
                                },
                            ]}
                            selected={customHeader?.header_alignment}
                            onChange={(value) =>
                                handleChangeHeader("header_alignment", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Position"
                            choices={[
                                {
                                    label: "Inline",
                                    value: "INLINE",
                                },
                                {
                                    label: "Inline secondary",
                                    value: "INLINE_SECONDARY",
                                },
                                {
                                    label: "Start",
                                    value: "START",
                                },
                                {
                                    label: "Small",
                                    value: "SMALL",
                                },
                            ]}
                            selected={customHeader?.header_position}
                            onChange={(value) =>
                                handleChangeHeader("header_position", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <TextField
                            label="Logo max width"
                            type="number"
                            value={customHeader?.header_logo_max_width}
                            onChange={(value) =>
                                handleChangeHeaderLogoMaxWidth(
                                    "header_logo_max_width",
                                    value
                                )
                            }
                            autoComplete="off"
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
            {customHeader?.header_banner_image_id !== "" ? (
                <LegacyCard.Section
                    title="SELECTED BANNER IMAGE"
                    actions={[
                        {
                            content: "Reset images",
                            destructive: true,
                            onAction: handleResetImagesHeader,
                        },
                    ]}
                >
                    <Thumbnail
                        size="medium"
                        source={selectedBannerImage?.src}
                    />
                </LegacyCard.Section>
            ) : (
                <LegacyCard.Section title="SELECTED BANNER IMAGE">
                    <SkeletonThumbnail size="medium" />
                </LegacyCard.Section>
            )}
            {mediaImages?.length ? (
                <LegacyCard.Section title="LOADED IMAGES">
                    <LegacyStack>
                        {mediaImages?.map((image, index) => (
                            <div
                                onClick={() =>
                                    handleCustomHeaderImage(
                                        "header_banner_image_id",
                                        image?.id
                                    )
                                }
                            >
                                <LegacyStack.Item key={index}>
                                    <Thumbnail
                                        size="medium"
                                        source={image?.src}
                                    />
                                </LegacyStack.Item>
                            </div>
                        ))}
                    </LegacyStack>
                </LegacyCard.Section>
            ) : (
                ""
            )}
            {customHeader?.header_logo_image_id !== "" ? (
                <LegacyCard.Section
                    title="SELECTED LOGO"
                    actions={[
                        {
                            content: "Reset images",
                            destructive: true,
                            onAction: handleResetImagesHeader,
                        },
                    ]}
                >
                    <Thumbnail size="medium" source={selectedLogoImage?.src} />
                </LegacyCard.Section>
            ) : (
                <LegacyCard.Section title="SELECTED LOGO">
                    <SkeletonThumbnail size="medium" />
                </LegacyCard.Section>
            )}
            {mediaImages?.length ? (
                <LegacyCard.Section title="LOADED IMAGES">
                    <LegacyStack>
                        {mediaImages?.map((image, index) => (
                            <div
                                onClick={() =>
                                    handleCustomHeaderImage(
                                        "header_logo_image_id",
                                        image?.id
                                    )
                                }
                            >
                                <LegacyStack.Item key={index}>
                                    <Thumbnail
                                        size="medium"
                                        source={image?.src}
                                    />
                                </LegacyStack.Item>
                            </div>
                        ))}
                    </LegacyStack>
                </LegacyCard.Section>
            ) : (
                ""
            )}
            <LegacyCard.Section>
                <div className="flex justify-end">
                    <Button
                        onClick={handleLoadMore}
                        loading={loadMoreBtnLoading}
                    >
                        Load images
                    </Button>
                </div>
            </LegacyCard.Section>
        </>
    );
}
