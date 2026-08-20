import { ChoiceList, LegacyCard, LegacyStack } from "@shopify/polaris";
import React from "react";

export default function CustomMerchandiseThumbnail({
    customMerchandiseThumbnail,
    handleChangeMerchandiseThumbnail,
}) {
    return (
        <LegacyCard.Section>
            <LegacyStack vertical>
                <LegacyStack.Item>
                    <ChoiceList
                        title="Thumbnail border"
                        choices={[
                            {
                                label: "Full",
                                value: "FULL",
                            },
                            {
                                label: "None",
                                value: "NONE",
                            },
                        ]}
                        selected={
                            customMerchandiseThumbnail?.merchandise_border
                        }
                        onChange={(value) =>
                            handleChangeMerchandiseThumbnail(
                                "merchandise_border",
                                value
                            )
                        }
                    />
                </LegacyStack.Item>
                <LegacyStack.Item>
                    <ChoiceList
                        title="Thumbnail corner radius"
                        choices={[
                            {
                                label: "Base",
                                value: "BASE",
                            },
                            {
                                label: "Large",
                                value: "LARGE",
                            },
                            {
                                label: "None",
                                value: "NONE",
                            },
                            {
                                label: "Small",
                                value: "SMALL",
                            },
                        ]}
                        selected={
                            customMerchandiseThumbnail?.merchandise_radius
                        }
                        onChange={(value) =>
                            handleChangeMerchandiseThumbnail(
                                "merchandise_radius",
                                value
                            )
                        }
                    />
                </LegacyStack.Item>
            </LegacyStack>
        </LegacyCard.Section>
    );
}
