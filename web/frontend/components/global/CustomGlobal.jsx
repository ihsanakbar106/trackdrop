import { ChoiceList, LegacyCard, LegacyStack } from "@shopify/polaris";
import React from "react";

export default function CustomGlobal({ customGlobal, handleChangeGlobal }) {
    return (
        <LegacyCard.Section>
            <LegacyStack vertical>
                <LegacyStack.Item>
                    <ChoiceList
                        title="Corner radius"
                        choices={[{ label: "None", value: "NONE" }]}
                        selected={customGlobal?.global_corner_radius}
                        onChange={(value) =>
                            handleChangeGlobal("global_corner_radius", value)
                        }
                    />
                </LegacyStack.Item>
                <LegacyStack.Item>
                    <ChoiceList
                        title="Typography kerning"
                        choices={[
                            { label: "Base", value: "BASE" },
                            { label: "Extra loose", value: "EXTRA_LOOSE" },
                            { label: "Loose", value: "LOOSE" },
                        ]}
                        selected={customGlobal?.global_typo_kerning}
                        onChange={(value) =>
                            handleChangeGlobal("global_typo_kerning", value)
                        }
                    />
                </LegacyStack.Item>
                <LegacyStack.Item>
                    <ChoiceList
                        title="Typography letter case"
                        choices={[
                            { label: "None", value: "NONE" },
                            { label: "Lower", value: "LOWER" },
                            { label: "Title", value: "TITLE" },
                            { label: "Upper", value: "UPPER" },
                        ]}
                        selected={customGlobal?.global_typo_case}
                        onChange={(value) =>
                            handleChangeGlobal("global_typo_case", value)
                        }
                    />
                </LegacyStack.Item>
            </LegacyStack>
        </LegacyCard.Section>
    );
}
