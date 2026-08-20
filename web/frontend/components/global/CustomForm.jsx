import { ChoiceList, LegacyCard, LegacyStack } from "@shopify/polaris";
import React from "react";

export default function CustomForm({ customForm, handleChangeForm }) {
    return (
        <>
            <LegacyCard.Section title="CHECKBOXES">
                <LegacyStack>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Corner radius"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Large", value: "LARGE" },
                                { label: "None", value: "NONE" },
                                { label: "Small", value: "SMALL" },
                            ]}
                            selected={customForm?.checkbox_corner_radius}
                            onChange={(value) =>
                                handleChangeForm(
                                    "checkbox_corner_radius",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
            <LegacyCard.Section title="CONTROLS">
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Corner radius"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Large", value: "LARGE" },
                                { label: "None", value: "NONE" },
                                { label: "Small", value: "SMALL" },
                            ]}
                            selected={customForm?.control_corner_radius}
                            onChange={(value) =>
                                handleChangeForm("control_corner_radius", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Border"
                            choices={[
                                { label: "Full", value: "FULL" },
                                { label: "None", value: "NONE" },
                            ]}
                            selected={customForm?.control_border}
                            onChange={(value) =>
                                handleChangeForm("control_border", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Color"
                            choices={[
                                { label: "Transparent", value: "TRANSPARENT" },
                            ]}
                            selected={customForm?.control_color}
                            onChange={(value) =>
                                handleChangeForm("control_color", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Label position"
                            choices={[
                                { label: "Inside", value: "INSIDE" },
                                { label: "Outside", value: "OUTSIDE" },
                            ]}
                            selected={customForm?.control_label_position}
                            onChange={(value) =>
                                handleChangeForm(
                                    "control_label_position",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
            <LegacyCard.Section title="SELECTS">
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Border"
                            choices={[
                                { label: "Block end", value: "BLOCK_END" },
                                { label: "Full", value: "FULL" },
                                { label: "None", value: "NONE" },
                            ]}
                            selected={customForm?.select_border}
                            onChange={(value) =>
                                handleChangeForm("select_border", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Typography font"
                            choices={[
                                { label: "Primary", value: "PRIMARY" },
                                { label: "Secondary", value: "SECONDARY" },
                            ]}
                            selected={customForm?.select_typo_font}
                            onChange={(value) =>
                                handleChangeForm("select_typo_font", value)
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
                            selected={customForm?.select_typo_kerning}
                            onChange={(value) =>
                                handleChangeForm("select_typo_kerning", value)
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
                            selected={customForm?.select_typo_case}
                            onChange={(value) =>
                                handleChangeForm("select_typo_case", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Typography size"
                            choices={[
                                { label: "Base", value: "BASE" },
                                {
                                    label: "Extra extra large",
                                    value: "EXTRA_EXTRA_LARGE",
                                },
                                { label: "Extra large", value: "EXTRA_LARGE" },
                                { label: "Extra small", value: "EXTRA_SMALL" },
                                { label: "Large", value: "LARGE" },
                                { label: "Medium", value: "MEDIUM" },
                                { label: "Small", value: "SMALL" },
                            ]}
                            selected={customForm?.select_typo_size}
                            onChange={(value) =>
                                handleChangeForm("select_typo_size", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Typography weight"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Bold", value: "BOLD" },
                            ]}
                            selected={customForm?.select_typo_weight}
                            onChange={(value) =>
                                handleChangeForm("select_typo_weight", value)
                            }
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
            <LegacyCard.Section title="TEXT FIELDS">
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Field border"
                            choices={[
                                { label: "Block end", value: "BLOCK_END" },
                                { label: "Full", value: "FULL" },
                                { label: "None", value: "NONE" },
                            ]}
                            selected={customForm?.text_border}
                            onChange={(value) =>
                                handleChangeForm("text_border", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Field typography font"
                            choices={[
                                { label: "Primary", value: "PRIMARY" },
                                { label: "Secondary", value: "SECONDARY" },
                            ]}
                            selected={customForm?.text_typo_font}
                            onChange={(value) =>
                                handleChangeForm("text_typo_font", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Field typography kerning"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Extra loose", value: "EXTRA_LOOSE" },
                                { label: "Loose", value: "LOOSE" },
                            ]}
                            selected={customForm?.text_typo_kerning}
                            onChange={(value) =>
                                handleChangeForm("text_typo_kerning", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Field typography letter case"
                            choices={[
                                { label: "None", value: "NONE" },
                                { label: "Lower", value: "LOWER" },
                                { label: "Title", value: "TITLE" },
                                { label: "Upper", value: "UPPER" },
                            ]}
                            selected={customForm?.text_typo_case}
                            onChange={(value) =>
                                handleChangeForm("text_typo_case", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Field typography size"
                            choices={[
                                { label: "Base", value: "BASE" },
                                {
                                    label: "Extra extra large",
                                    value: "EXTRA_EXTRA_LARGE",
                                },
                                { label: "Extra large", value: "EXTRA_LARGE" },
                                { label: "Extra small", value: "EXTRA_SMALL" },
                                { label: "Large", value: "LARGE" },
                                { label: "Medium", value: "MEDIUM" },
                                { label: "Small", value: "SMALL" },
                            ]}
                            selected={customForm?.text_typo_size}
                            onChange={(value) =>
                                handleChangeForm("text_typo_size", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Field typography weight"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Bold", value: "BOLD" },
                            ]}
                            selected={customForm?.text_typo_weight}
                            onChange={(value) =>
                                handleChangeForm("text_typo_weight", value)
                            }
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
        </>
    );
}
