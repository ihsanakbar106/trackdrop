import {
    Button,
    ChoiceList,
    LegacyCard,
    LegacyStack,
    SkeletonThumbnail,
    Thumbnail,
} from "@shopify/polaris";

export default function CustomMain({
    customMain,
    handleChangeMain,
    mediaImages,
    handleCustomMainImage,
    handleLoadMore,
    loadMoreBtnLoading,
    handleResetImagesMain,
}) {
    const selectedImage = mediaImages?.find(
        (image) => image?.id === customMain?.main_bg_image_id
    );
    return (
        <>
            <LegacyCard.Section>
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Color scheme"
                            choices={[
                                {
                                    label: "Transparent",
                                    value: "TRANSPARENT",
                                },
                                {
                                    label: "Color scheme 1",
                                    value: "COLOR_SCHEME1",
                                },
                                {
                                    label: "Color scheme 2",
                                    value: "COLOR_SCHEME2",
                                },
                            ]}
                            selected={customMain?.main_color_scheme}
                            onChange={(value) =>
                                handleChangeMain("main_color_scheme", value)
                            }
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
            {customMain?.main_bg_image_id !== "" ? (
                <LegacyCard.Section
                    title="SELECTED BACKGROUND IMAGE"
                    actions={[
                        {
                            content: "Reset images",
                            destructive: true,
                            onAction: handleResetImagesMain,
                        },
                    ]}
                >
                    <Thumbnail size="medium" source={selectedImage?.src} />
                </LegacyCard.Section>
            ) : (
                <LegacyCard.Section title="SELECTED BACKGROUND IMAGE">
                    <SkeletonThumbnail size="medium" />
                </LegacyCard.Section>
            )}
            {mediaImages?.length > 0 ? (
                <LegacyCard.Section title="LOADED IMAGES">
                    <LegacyStack>
                        {mediaImages?.map((image, index) => (
                            <div
                                onClick={() =>
                                    handleCustomMainImage(
                                        "main_bg_image_id",
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
