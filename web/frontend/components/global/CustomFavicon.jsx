import {
    Button,
    InlineGrid,
    LegacyCard,
    LegacyStack,
    SkeletonThumbnail,
    Thumbnail,
} from "@shopify/polaris";
import React, { useCallback, useState } from "react";

export default function CustomFavicon({
    mediaImages,
    faviconImageId,
    handleCustomFavicon,
    handleLoadMore,
    loadMoreBtnLoading,
    handleResetImages,
}) {
    const selectedFavicon = mediaImages?.find(
        (image) => image?.id == faviconImageId
    );

    return (
        <>
            {/* <LegacyCard
                title={tabs[selected].content.toUpperCase()}
                secondaryFooterActions={[
                    {
                        content: "Load images",
                        onAction: handleLoadMore,
                        loading: loadMoreBtnLoading,
                    },
                ]}
            > */}
            {/* <LegacyCard.Section title="SEARCH">
                <TextField
                    label="File name"
                    value={valueFileName}
                    onChange={handleChangeFileName}
                    autoComplete="off"
                />
            </LegacyCard.Section> */}
            {faviconImageId !== "" ? (
                <LegacyCard.Section
                    title="SELECTED FAVICON"
                    actions={[
                        {
                            content: "Reset images",
                            destructive: true,
                            onAction: handleResetImages,
                        },
                    ]}
                >
                    <InlineGrid>
                        <Thumbnail
                            size="medium"
                            source={selectedFavicon?.src}
                        />
                    </InlineGrid>
                </LegacyCard.Section>
            ) : (
                <LegacyCard.Section title="SELECTED FAVICON">
                    <SkeletonThumbnail size="medium" />
                </LegacyCard.Section>
            )}
            {mediaImages?.length ? (
                <LegacyCard.Section title="LOADED IMAGES">
                    <LegacyStack>
                        {mediaImages?.map((image, index) => (
                            <div onClick={() => handleCustomFavicon(image?.id)}>
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
            {/* </LegacyCard> */}
        </>
    );
}
