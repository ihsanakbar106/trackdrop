import {
    ChoiceList,
    LegacyCard,
    LegacyStack,
    TextField,
    Thumbnail,
} from "@shopify/polaris";
import React from "react";

export default function CustomHeading({ customHeading, handleChangeHeading }) {
    return (
        <>
            <LegacyCard.Section title="HEADING LEVEL 1">
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 1 typography font"
                            choices={[
                                { label: "Primary", value: "PRIMARY" },
                                { label: "Secondary", value: "SECONDARY" },
                            ]}
                            selected={customHeading?.head1_typo_font}
                            onChange={(value) =>
                                handleChangeHeading("head1_typo_font", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 1 typography kerning"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Extra loose", value: "EXTRA_LOOSE" },
                                { label: "Loose", value: "LOOSE" },
                            ]}
                            selected={customHeading?.head1_typo_kerning}
                            onChange={(value) =>
                                handleChangeHeading("head1_typo_kerning", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 1 typography letter case"
                            choices={[
                                { label: "None", value: "NONE" },
                                { label: "Lower", value: "LOWER" },
                                { label: "Title", value: "TITLE" },
                                { label: "Upper", value: "UPPER" },
                            ]}
                            selected={customHeading?.head1_btn_typo_case}
                            onChange={(value) =>
                                handleChangeHeading(
                                    "head1_btn_typo_case",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 1 typography size"
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
                            selected={customHeading?.head1_btn_typo_size}
                            onChange={(value) =>
                                handleChangeHeading(
                                    "head1_btn_typo_size",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
            <LegacyCard.Section title="HEADING LEVEL 2">
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 2 typography font"
                            choices={[
                                { label: "Primary", value: "PRIMARY" },
                                { label: "Secondary", value: "SECONDARY" },
                            ]}
                            selected={customHeading?.head2_typo_font}
                            onChange={(value) =>
                                handleChangeHeading("head2_typo_font", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 2 typography kerning"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Extra loose", value: "EXTRA_LOOSE" },
                                { label: "Loose", value: "LOOSE" },
                            ]}
                            selected={customHeading?.head2_typo_kerning}
                            onChange={(value) =>
                                handleChangeHeading("head2_typo_kerning", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 2 typography letter case"
                            choices={[
                                { label: "None", value: "NONE" },
                                { label: "Lower", value: "LOWER" },
                                { label: "Title", value: "TITLE" },
                                { label: "Upper", value: "UPPER" },
                            ]}
                            selected={customHeading?.head2_btn_typo_case}
                            onChange={(value) =>
                                handleChangeHeading(
                                    "head2_btn_typo_case",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 2 typography size"
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
                            selected={customHeading?.head2_btn_typo_size}
                            onChange={(value) =>
                                handleChangeHeading(
                                    "head2_btn_typo_size",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
            <LegacyCard.Section title="HEADING LEVEL 3">
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 3 typography font"
                            choices={[
                                { label: "Primary", value: "PRIMARY" },
                                { label: "Secondary", value: "SECONDARY" },
                            ]}
                            selected={customHeading?.head3_typo_font}
                            onChange={(value) =>
                                handleChangeHeading("head3_typo_font", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 3 typography kerning"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Extra loose", value: "EXTRA_LOOSE" },
                                { label: "Loose", value: "LOOSE" },
                            ]}
                            selected={customHeading?.head3_typo_kerning}
                            onChange={(value) =>
                                handleChangeHeading("head3_typo_kerning", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 3 typography letter case"
                            choices={[
                                { label: "None", value: "NONE" },
                                { label: "Lower", value: "LOWER" },
                                { label: "Title", value: "TITLE" },
                                { label: "Upper", value: "UPPER" },
                            ]}
                            selected={customHeading?.head3_btn_typo_case}
                            onChange={(value) =>
                                handleChangeHeading(
                                    "head3_btn_typo_case",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Level 3 typography size"
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
                            selected={customHeading?.head3_btn_typo_size}
                            onChange={(value) =>
                                handleChangeHeading(
                                    "head3_btn_typo_size",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
        </>
    );
}
