import {
    ChoiceList,
    LegacyCard,
    LegacyStack,
    TextField,
    Thumbnail,
} from "@shopify/polaris";
import React from "react";

export default function CustomButton({ customButton, handleChangeButton }) {
    return (
        <>
            <LegacyCard.Section title="PRIMARY BUTTONS">
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button background"
                            choices={[
                                { label: "None", value: "NONE" },
                                { label: "Solid", value: "SOLID" },
                            ]}
                            selected={customButton?.prim_btn_bg}
                            onChange={(value) =>
                                handleChangeButton("prim_btn_bg", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button block padding"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Extra loose", value: "EXTRA_LOOSE" },
                                { label: "Extra tight", value: "EXTRA_TIGHT" },
                                { label: "Loose", value: "LOOSE" },
                                { label: "None", value: "NONE" },
                                { label: "Tight", value: "TIGHT" },
                            ]}
                            selected={customButton?.prim_btn_block_padding}
                            onChange={(value) =>
                                handleChangeButton(
                                    "prim_btn_block_padding",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button border"
                            choices={[
                                { label: "Full", value: "FULL" },
                                { label: "None", value: "NONE" },
                            ]}
                            selected={customButton?.prim_btn_border}
                            onChange={(value) =>
                                handleChangeButton("prim_btn_border", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Corner radius"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Large", value: "LARGE" },
                                { label: "None", value: "NONE" },
                                { label: "Small", value: "SMALL" },
                            ]}
                            selected={customButton?.prim_btn_corner_radius}
                            onChange={(value) =>
                                handleChangeButton(
                                    "prim_btn_corner_radius",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button inline padding"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Extra loose", value: "EXTRA_LOOSE" },
                                { label: "Extra tight", value: "EXTRA_TIGHT" },
                                { label: "Loose", value: "LOOSE" },
                                { label: "None", value: "NONE" },
                                { label: "Tight", value: "TIGHT" },
                            ]}
                            selected={customButton?.prim_btn_inline_padding}
                            onChange={(value) =>
                                handleChangeButton(
                                    "prim_btn_inline_padding",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button typography font"
                            choices={[
                                { label: "Primary", value: "PRIMARY" },
                                { label: "Secondary", value: "SECONDARY" },
                            ]}
                            selected={customButton?.prim_btn_typo_font}
                            onChange={(value) =>
                                handleChangeButton("prim_btn_typo_font", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button typography kerning"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Extra loose", value: "EXTRA_LOOSE" },
                                { label: "Loose", value: "LOOSE" },
                            ]}
                            selected={customButton?.prim_btn_typo_kerning}
                            onChange={(value) =>
                                handleChangeButton(
                                    "prim_btn_typo_kerning",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button typography letter case"
                            choices={[
                                { label: "None", value: "NONE" },
                                { label: "Lower", value: "LOWER" },
                                { label: "Title", value: "TITLE" },
                                { label: "Upper", value: "UPPER" },
                            ]}
                            selected={customButton?.prim_btn_typo_case}
                            onChange={(value) =>
                                handleChangeButton("prim_btn_typo_case", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button typography size"
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
                            selected={customButton?.prim_btn_typo_size}
                            onChange={(value) =>
                                handleChangeButton("prim_btn_typo_size", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button typography weight"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Bold", value: "BOLD" },
                            ]}
                            selected={customButton?.prim_btn_typo_weight}
                            onChange={(value) =>
                                handleChangeButton(
                                    "prim_btn_typo_weight",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
            <LegacyCard.Section title="SECONDARY BUTTONS">
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button background"
                            choices={[
                                { label: "None", value: "NONE" },
                                { label: "Solid", value: "SOLID" },
                            ]}
                            selected={customButton?.secon_btn_bg}
                            onChange={(value) =>
                                handleChangeButton("secon_btn_bg", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button block padding"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Extra loose", value: "EXTRA_LOOSE" },
                                { label: "Extra tight", value: "EXTRA_TIGHT" },
                                { label: "Loose", value: "LOOSE" },
                                { label: "None", value: "NONE" },
                                { label: "Tight", value: "TIGHT" },
                            ]}
                            selected={customButton?.secon_btn_block_padding}
                            onChange={(value) =>
                                handleChangeButton(
                                    "secon_btn_block_padding",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button border"
                            choices={[
                                { label: "Full", value: "FULL" },
                                { label: "None", value: "NONE" },
                            ]}
                            selected={customButton?.secon_btn_border}
                            onChange={(value) =>
                                handleChangeButton("secon_btn_border", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Corner radius"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Large", value: "LARGE" },
                                { label: "None", value: "NONE" },
                                { label: "Small", value: "SMALL" },
                            ]}
                            selected={customButton?.secon_btn_corner_radius}
                            onChange={(value) =>
                                handleChangeButton(
                                    "secon_btn_corner_radius",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button inline padding"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Extra loose", value: "EXTRA_LOOSE" },
                                { label: "Extra tight", value: "EXTRA_TIGHT" },
                                { label: "Loose", value: "LOOSE" },
                                { label: "None", value: "NONE" },
                                { label: "Tight", value: "TIGHT" },
                            ]}
                            selected={customButton?.secon_btn_inline_padding}
                            onChange={(value) =>
                                handleChangeButton(
                                    "secon_btn_inline_padding",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button typography font"
                            choices={[
                                { label: "Primary", value: "PRIMARY" },
                                { label: "Secondary", value: "SECONDARY" },
                            ]}
                            selected={customButton?.secon_btn_typo_font}
                            onChange={(value) =>
                                handleChangeButton("secon_btn_typo_font", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button typography kerning"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Extra loose", value: "EXTRA_LOOSE" },
                                { label: "Loose", value: "LOOSE" },
                            ]}
                            selected={customButton?.secon_btn_typo_kerning}
                            onChange={(value) =>
                                handleChangeButton(
                                    "secon_btn_typo_kerning",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button typography letter case"
                            choices={[
                                { label: "None", value: "NONE" },
                                { label: "Lower", value: "LOWER" },
                                { label: "Title", value: "TITLE" },
                                { label: "Upper", value: "UPPER" },
                            ]}
                            selected={customButton?.secon_btn_typo_case}
                            onChange={(value) =>
                                handleChangeButton("secon_btn_typo_case", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button typography size"
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
                            selected={customButton?.secon_btn_typo_size}
                            onChange={(value) =>
                                handleChangeButton("secon_btn_typo_size", value)
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Button typography weight"
                            choices={[
                                { label: "Base", value: "BASE" },
                                { label: "Bold", value: "BOLD" },
                            ]}
                            selected={customButton?.secon_btn_typo_weight}
                            onChange={(value) =>
                                handleChangeButton(
                                    "secon_btn_typo_weight",
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
