import {
    ChoiceList,
    LegacyCard,
    LegacyStack,
    Select,
    TextField,
} from "@shopify/polaris";
import React from "react";

export default function CustomTypography({
    customTypography,
    handleChangeTypography,
    handleChangeTypographyField,
}) {
    const typoSizeOptions = [
        { label: "Select", value: "" },
        { label: "12", value: "12" },
        { label: "13", value: "13" },
        { label: "14", value: "14" },
        { label: "15", value: "15" },
        { label: "16", value: "16" },
        { label: "17", value: "17" },
        { label: "18", value: "18" },
    ];
    const typoRadiusOptions = [
        { label: "Select", value: "" },
        { label: "1", value: "1" },
        { label: "1.1", value: "1.1" },
        { label: "1.2", value: "1.2" },
    ];
    const primBoldOptions = [
        { label: "Select", value: "" },
        { label: "100", value: "100" },
        { label: "200", value: "200" },
        { label: "300", value: "300" },
        { label: "400", value: "400" },
        { label: "500", value: "500" },
        { label: "600", value: "600" },
        { label: "700", value: "700" },
    ];
    const primBaseOptions = [
        { label: "Select", value: "" },
        { label: "100", value: "100" },
        { label: "200", value: "200" },
        { label: "300", value: "300" },
        { label: "400", value: "400" },
    ];
    const secoBoldOptions = [
        { label: "Select", value: "" },
        { label: "400", value: "400" },
    ];

    const options = [
        { label: "Select", value: "" },
        { label: "Mono", value: "Mono" },
        { label: "Sans-serif", value: "Sans-serif" },
        { label: "Serif", value: "Serif" },
        { label: "Abel", value: "Abel" },
        { label: "Abril Fatface", value: "Abril Fatface" },
        { label: "Agmena", value: "Agmena" },
        { label: "Akko", value: "Akko" },
        { label: "Alegreya", value: "Alegreya" },
        { label: "Alegreya Sans", value: "Alegreya Sans" },
        { label: "Alfie", value: "Alfie" },
        { label: "Americana", value: "Americana" },
        { label: "Amiri", value: "Amiri" },
        { label: "Anonymous Pro", value: "Anonymous Pro" },
        { label: "Antique Olive", value: "Antique Olive" },
        { label: "Arapey", value: "Arapey" },
        { label: "Archivo", value: "Archivo" },
        { label: "Archivo Narrow", value: "Archivo Narrow" },
        { label: "Arimo", value: "Arimo" },
        { label: "Armata", value: "Armata" },
        { label: "Arvo", value: "Arvo" },
        { label: "Asap", value: "Asap" },
        { label: "Assistant", value: "Assistant" },
        { label: "Asul", value: "Asul" },
        { label: "Avenir Next", value: "Avenir Next" },
        { label: "Avenir Next Rounded", value: "Avenir Next Rounded" },
        { label: "Azbuka", value: "Azbuka" },
        { label: "Basic Commercial", value: "Basic Commercial" },
        {
            label: "Basic Commercial Soft Rounded",
            value: "Basic Commercial Soft Rounded",
        },
        { label: "Baskerville No 2", value: "Baskerville No 2" },
        { label: "Bauer Bodoni", value: "Bauer Bodoni" },
        { label: "Beefcakes", value: "Beefcakes" },
        { label: "Bembo Book", value: "Bembo Book" },
        { label: "Bernhard Modern", value: "Bernhard Modern" },
        { label: "Bio Rhyme", value: "Bio Rhyme" },
        { label: "Bitter", value: "Bitter" },
        { label: "Bodoni Poster", value: "Bodoni Poster" },
        { label: "Burlingame", value: "Burlingame" },
        { label: "Cabin", value: "Cabin" },
        { label: "Cachet", value: "Cachet" },
        { label: "Cardamon", value: "Cardamon" },
        { label: "Cardo", value: "Cardo" },
        { label: "Carter Sans", value: "Carter Sans" },
        { label: "Caslon Bold", value: "Caslon Bold" },
        { label: "Caslon Old Face", value: "Caslon Old Face" },
        { label: "Catamaran", value: "Catamaran" },
        { label: "Centaur", value: "Centaur" },
        { label: "Century Gothic", value: "Century Gothic" },
        { label: "Chivo", value: "Chivo" },
        { label: "Chong Modern", value: "Chong Modern" },
        { label: "Claire News", value: "Claire News" },
        { label: "Cooper BT", value: "Cooper BT" },
        { label: "Cormorant", value: "Cormorant" },
        { label: "Courier New", value: "Courier New" },
        { label: "Crimson Text", value: "Crimson Text" },
        { label: "DIN Neuzeit Grotesk", value: "DIN Neuzeit Grotesk" },
        { label: "DIN Next", value: "DIN Next" },
        { label: "DIN Next Slab", value: "DIN Next Slab" },
        { label: "DM Sans", value: "DM Sans" },
        { label: "Daytona", value: "Daytona" },
        { label: "Domine", value: "Domine" },
        { label: "Dosis", value: "Dosis" },
        { label: "Eczar", value: "Eczar" },
        { label: "Electra", value: "Electra" },
        { label: "Eurostile Next", value: "Eurostile Next" },
        { label: "FF Meta", value: "FF Meta" },
        { label: "FF Meta Serif", value: "FF Meta Serif" },
        { label: "FF Tisa", value: "FF Tisa" },
        { label: "FF Tisa Sans", value: "FF Tisa Sans" },
        { label: "FF Unit", value: "FF Unit" },
        { label: "FF Unit Rounded", value: "FF Unit Rounded" },
        { label: "FF Unit Slab", value: "FF Unit Slab" },
        { label: "FS Kim", value: "FS Kim" },
        { label: "FS Koopman", value: "FS Koopman" },
        { label: "FS Siena", value: "FS Siena" },
        { label: "Fette Gotisch", value: "Fette Gotisch" },
        { label: "Fira Sans", value: "Fira Sans" },
        { label: "Fjalla One", value: "Fjalla One" },
        { label: "Friz Quadrata", value: "Friz Quadrata" },
        { label: "Frutiger Serif", value: "Frutiger Serif" },
        { label: "Futura", value: "Futura" },
        { label: "Futura Black", value: "Futura Black" },
        { label: "Garamond", value: "Garamond" },
        { label: "Geometric 415", value: "Geometric 415" },
        { label: "Georgia Pro", value: "Georgia Pro" },
        { label: "Gill Sans Nova", value: "Gill Sans Nova" },
        { label: "Glegoo", value: "Glegoo" },
        { label: "Goudy Old Style", value: "Goudy Old Style" },
        { label: "Harmonia Sans", value: "Harmonia Sans" },
        { label: "Helvetica", value: "Helvetica" },
        { label: "Hope Sans", value: "Hope Sans" },
        { label: "Humanist 521", value: "Humanist 521" },
        { label: "IBM Plex Sans", value: "IBM Plex Sans" },
        { label: "ITC Avant Garde Gothic", value: "ITC Avant Garde Gothic" },
        { label: "ITC Benguiat", value: "ITC Benguiat" },
        { label: "ITC Berkeley Old Style", value: "ITC Berkeley Old Style" },
        { label: "ITC Bodoni Seventytwo", value: "ITC Bodoni Seventytwo" },
        { label: "ITC Bodoni Twelve", value: "ITC Bodoni Twelve" },
        { label: "ITC Caslon No 224", value: "ITC Caslon No 224" },
        { label: "ITC Charter", value: "ITC Charter" },
        { label: "ITC Cheltenham", value: "ITC Cheltenham" },
        { label: "ITC Clearface", value: "ITC Clearface" },
        { label: "ITC Conduit", value: "ITC Conduit" },
        { label: "ITC Esprit", value: "ITC Esprit" },
        { label: "ITC Founders Caslon", value: "ITC Founders Caslon" },
        { label: "ITC Franklin Gothic", value: "ITC Franklin Gothic" },
        { label: "ITC Galliard", value: "ITC Galliard" },
        { label: "ITC Gamma", value: "ITC Gamma" },
        { label: "ITC Goudy Sans", value: "ITC Goudy Sans" },
        { label: "ITC Johnston", value: "ITC Johnston" },
        { label: "ITC Mendoza Roman", value: "ITC Mendoza Roman" },
        { label: "ITC Modern No 216", value: "ITC Modern No 216" },
        { label: "ITC New Baskerville", value: "ITC New Baskerville" },
        { label: "ITC New Esprit", value: "ITC New Esprit" },
        { label: "ITC New Veljovic", value: "ITC New Veljovic" },
        { label: "ITC Novarese", value: "ITC Novarese" },
        { label: "ITC Officina Sans", value: "ITC Officina Sans" },
        { label: "ITC Officina Serif", value: "ITC Officina Serif" },
        { label: "ITC Stepp", value: "ITC Stepp" },
        { label: "ITC Stone Humanist", value: "ITC Stone Humanist" },
        { label: "ITC Stone Informal", value: "ITC Stone Informal" },
        { label: "ITC Stone Sans II", value: "ITC Stone Sans II" },
        { label: "ITC Stone Serif", value: "ITC Stone Serif" },
        { label: "ITC Tapioca", value: "ITC Tapioca" },
        { label: "Inconsolata", value: "Inconsolata" },
        { label: "Inknut Antiqua", value: "Inknut Antiqua" },
        { label: "Inter", value: "Inter" },
        { label: "Joanna Nova", value: "Joanna Nova" },
        { label: "Joanna Sans Nova", value: "Joanna Sans Nova" },
        { label: "Josefin Sans", value: "Josefin Sans" },
        { label: "Josefin Slab", value: "Josefin Slab" },
        { label: "Kairos", value: "Kairos" },
        { label: "Kalam", value: "Kalam" },
        { label: "Karla", value: "Karla" },
        { label: "Kreon", value: "Kreon" },
        { label: "Lato", value: "Lato" },
        { label: "Laurentian", value: "Laurentian" },
        { label: "Libelle", value: "Libelle" },
        { label: "Libre Baskerville", value: "Libre Baskerville" },
        { label: "Libre Franklin", value: "Libre Franklin" },
        { label: "Linotype Didot", value: "Linotype Didot" },
        { label: "Linotype Gianotten", value: "Linotype Gianotten" },
        { label: "Linotype Really", value: "Linotype Really" },
        { label: "Linotype Syntax Serif", value: "Linotype Syntax Serif" },
        { label: "Lobster", value: "Lobster" },
        { label: "Lobster Two", value: "Lobster Two" },
        { label: "Lora", value: "Lora" },
        { label: "Lucia", value: "Lucia" },
        { label: "Lucida Grande", value: "Lucida Grande" },
        { label: "Luthersche Fraktur", value: "Luthersche Fraktur" },
        { label: "Madera", value: "Madera" },
        { label: "Malabar", value: "Malabar" },
        { label: "Mariposa Sans", value: "Mariposa Sans" },
        { label: "Maven Pro", value: "Maven Pro" },
        { label: "Megrim", value: "Megrim" },
        { label: "Melior", value: "Melior" },
        { label: "Memphis", value: "Memphis" },
        { label: "Memphis Soft Rounded", value: "Memphis Soft Rounded" },
        { label: "Mentor Sans", value: "Mentor Sans" },
        { label: "Merriweather Sans", value: "Merriweather Sans" },
        { label: "Metro Nova", value: "Metro Nova" },
        { label: "Modern No 20", value: "Modern No 20" },
        { label: "Monaco", value: "Monaco" },
        { label: "Monotype Baskerville", value: "Monotype Baskerville" },
        { label: "Monotype Bodoni", value: "Monotype Bodoni" },
        {
            label: "Monotype Century Old Style",
            value: "Monotype Century Old Style",
        },
        { label: "Monotype Goudy", value: "Monotype Goudy" },
        { label: "Monotype Goudy Modern", value: "Monotype Goudy Modern" },
        {
            label: "Monotype Italian Old Style",
            value: "Monotype Italian Old Style",
        },
        { label: "Monotype New Clarendon", value: "Monotype New Clarendon" },
        { label: "Monotype News Gothic", value: "Monotype News Gothic" },
        { label: "Monotype Sabon", value: "Monotype Sabon" },
        { label: "Montserrat", value: "Montserrat" },
        { label: "Mouse Memoirs", value: "Mouse Memoirs" },
        { label: "Muli", value: "Muli" },
        { label: "Mundo Sans", value: "Mundo Sans" },
        { label: "Neo Sans", value: "Neo Sans" },
        { label: "Neue Aachen", value: "Neue Aachen" },
        { label: "Neue Frutiger 1450", value: "Neue Frutiger 1450" },
        { label: "Neue Haas Unica", value: "Neue Haas Unica" },
        { label: "Neue Plak", value: "Neue Plak" },
        { label: "Neue Swift", value: "Neue Swift" },
        { label: "Neuton", value: "Neuton" },
        { label: "Neuzeit Office", value: "Neuzeit Office" },
        {
            label: "Neuzeit Office Soft Rounded",
            value: "Neuzeit Office Soft Rounded",
        },
        { label: "Neuzeit S", value: "Neuzeit S" },
        { label: "New Century Schoolbook", value: "New Century Schoolbook" },
        { label: "News 702", value: "News 702" },
        { label: "News 705", value: "News 705" },
        { label: "News Cycle", value: "News Cycle" },
        { label: "News Gothic No 2", value: "News Gothic No 2" },
        { label: "News Plantin", value: "News Plantin" },
        { label: "Nobile", value: "Nobile" },
        { label: "Noticia Text", value: "Noticia Text" },
        { label: "Noto Serif", value: "Noto Serif" },
        { label: "Nunito", value: "Nunito" },
        { label: "Nunito Sans", value: "Nunito Sans" },
        { label: "Old Standard TT", value: "Old Standard TT" },
        { label: "Open Sans", value: "Open Sans" },
        { label: "Open Sans Condensed", value: "Open Sans Condensed" },
        { label: "Optima nova", value: "Optima nova" },
        { label: "Oswald", value: "Oswald" },
        { label: "Ovo", value: "Ovo" },
        { label: "Oxygen", value: "Oxygen" },
        { label: "PMN Caecilia", value: "PMN Caecilia" },
        { label: "Palatino", value: "Palatino" },
        { label: "Panton", value: "Panton" },
        { label: "Paprika", value: "Paprika" },
        { label: "Paralucent", value: "Paralucent" },
        { label: "Parchment", value: "Parchment" },
        { label: "Parisine", value: "Parisine" },
        { label: "Pathway Gothic One", value: "Pathway Gothic One" },
        { label: "Patrick Hand", value: "Patrick Hand" },
        { label: "Penna", value: "Penna" },
        { label: "Perpetua", value: "Perpetua" },
        { label: "Phantom Sans", value: "Phantom Sans" },
        { label: "Pier Sans", value: "Pier Sans" },
        { label: "Playfair Display", value: "Playfair Display" },
        { label: "Poppins", value: "Poppins" },
        { label: "Pragmatica", value: "Pragmatica" },
        { label: "Press Gothic", value: "Press Gothic" },
        { label: "Proxima Nova", value: "Proxima Nova" },
        { label: "Publico Text", value: "Publico Text" },
        { label: "Quantico", value: "Quantico" },
        { label: "Quattrocento", value: "Quattrocento" },
        { label: "Quicksand", value: "Quicksand" },
        { label: "Quincy CF", value: "Quincy CF" },
        { label: "Qwigley", value: "Qwigley" },
        { label: "Radley", value: "Radley" },
        { label: "Raleway", value: "Raleway" },
        { label: "Rasa", value: "Rasa" },
        { label: "Recoleta", value: "Recoleta" },
        { label: "Reem Kufi", value: "Reem Kufi" },
        { label: "Reliq", value: "Reliq" },
        { label: "Roboto", value: "Roboto" },
        { label: "Roboto Condensed", value: "Roboto Condensed" },
        { label: "Roboto Mono", value: "Roboto Mono" },
        { label: "Rockwell Nova", value: "Rockwell Nova" },
        { label: "Rosario", value: "Rosario" },
        { label: "Rubik", value: "Rubik" },
        { label: "Sagona", value: "Sagona" },
        { label: "Sansation", value: "Sansation" },
        { label: "Seravek", value: "Seravek" },
        { label: "Shrikhand", value: "Shrikhand" },
        { label: "Signika", value: "Signika" },
        { label: "Silom", value: "Silom" },
        { label: "Spectral", value: "Spectral" },
        { label: "Spinnaker", value: "Spinnaker" },
        { label: "Stalemate", value: "Stalemate" },
        { label: "Steelfish", value: "Steelfish" },
        { label: "Stratum", value: "Stratum" },
        { label: "Styrene", value: "Styrene" },
        { label: "Sumana", value: "Sumana" },
        { label: "Supra", value: "Supra" },
        { label: "Syncopate", value: "Syncopate" },
        { label: "Tahoma", value: "Tahoma" },
        { label: "Tempo Grunge", value: "Tempo Grunge" },
        { label: "Tempo Sans", value: "Tempo Sans" },
        { label: "Tenor Sans", value: "Tenor Sans" },
        { label: "Texta", value: "Texta" },
        { label: "Times New Roman", value: "Times New Roman" },
        { label: "Tisa Sans", value: "Tisa Sans" },
        { label: "Titillium Web", value: "Titillium Web" },
        { label: "Trade Gothic", value: "Trade Gothic" },
        { label: "Trajan Pro", value: "Trajan Pro" },
        { label: "Trebuchet MS", value: "Trebuchet MS" },
        { label: "Triade", value: "Triade" },
        { label: "Trocchi", value: "Trocchi" },
        { label: "Twentieth Century", value: "Twentieth Century" },
        { label: "Ubuntu", value: "Ubuntu" },
        { label: "Ubuntu Condensed", value: "Ubuntu Condensed" },
        { label: "Ultra", value: "Ultra" },
        { label: "Unica One", value: "Unica One" },
        { label: "Unna", value: "Unna" },
        { label: "Varela Round", value: "Varela Round" },
        { label: "Verdana", value: "Verdana" },
        { label: "Vesper Libre", value: "Vesper Libre" },
        { label: "Vollkorn", value: "Vollkorn" },
        { label: "Work Sans", value: "Work Sans" },
        { label: "Yanone Kaffeesatz", value: "Yanone Kaffeesatz" },
        { label: "Yellowtail", value: "Yellowtail" },
        { label: "Yeseva One", value: "Yeseva One" },
        { label: "Zilla Slab", value: "Zilla Slab" },
    ];

    return (
        <>
            <LegacyCard.Section title="PRIMARY">
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <Select
                            label="Name"
                            options={options}
                            onChange={(value) =>
                                handleChangeTypographyField(
                                    "typo_prim_shopi_name",
                                    value
                                )
                            }
                            value={customTypography?.typo_prim_shopi_name}
                            helpText="Font previews available at https://shopify.dev/docs/themes/architecture/settings/fonts#available-fonts"
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Loading strategy"
                            choices={[
                                { label: "Auto", value: "AUTO" },
                                { label: "Block", value: "BLOCK" },
                                { label: "Swap", value: "SWAP" },
                                { label: "Fallback", value: "FALLBACK" },
                                { label: "Optional", value: "OPTIONAL" },
                            ]}
                            selected={
                                customTypography?.typo_prim_shopi_strategy
                            }
                            onChange={(value) =>
                                handleChangeTypography(
                                    "typo_prim_shopi_strategy",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <Select
                            label="Base weight"
                            options={primBaseOptions}
                            onChange={(value) =>
                                handleChangeTypographyField(
                                    "typo_prim_shopi_base_weight",
                                    value
                                )
                            }
                            value={
                                customTypography?.typo_prim_shopi_base_weight
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <Select
                            label="Bold weight"
                            options={primBoldOptions}
                            onChange={(value) =>
                                handleChangeTypographyField(
                                    "typo_prim_shopi_bold_weight",
                                    value
                                )
                            }
                            value={
                                customTypography?.typo_prim_shopi_bold_weight
                            }
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
            <LegacyCard.Section title="SECONDARY">
                <LegacyStack vertical>
                    <LegacyStack.Item>
                        <Select
                            label="Name"
                            options={options}
                            onChange={(value) =>
                                handleChangeTypographyField(
                                    "typo_secon_shopi_name",
                                    value
                                )
                            }
                            value={customTypography?.typo_secon_shopi_name}
                            helpText="Font previews available at https://shopify.dev/docs/themes/architecture/settings/fonts#available-fonts"
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <ChoiceList
                            title="Loading strategy"
                            choices={[
                                { label: "Auto", value: "AUTO" },
                                { label: "Block", value: "BLOCK" },
                                { label: "Swap", value: "SWAP" },
                                { label: "Fallback", value: "FALLBACK" },
                                { label: "Optional", value: "OPTIONAL" },
                            ]}
                            selected={
                                customTypography?.typo_secon_shopi_strategy
                            }
                            onChange={(value) =>
                                handleChangeTypography(
                                    "typo_secon_shopi_strategy",
                                    value
                                )
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <Select
                            label="Base weight"
                            options={secoBoldOptions}
                            onChange={(value) =>
                                handleChangeTypographyField(
                                    "typo_secon_shopi_base_weight",
                                    value
                                )
                            }
                            value={
                                customTypography?.typo_secon_shopi_base_weight
                            }
                        />
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                        <Select
                            label="Bold weight"
                            options={secoBoldOptions}
                            onChange={(value) =>
                                handleChangeTypographyField(
                                    "typo_secon_shopi_bold_weight",
                                    value
                                )
                            }
                            value={
                                customTypography?.typo_secon_shopi_bold_weight
                            }
                        />
                    </LegacyStack.Item>
                </LegacyStack>
            </LegacyCard.Section>
            <LegacyCard.Section>
                <LegacyStack.Item>
                    <Select
                        label="Typography size base"
                        options={typoSizeOptions}
                        onChange={(value) =>
                            handleChangeTypographyField("typo_size_base", value)
                        }
                        value={customTypography?.typo_size_base}
                    />
                </LegacyStack.Item>
                <LegacyStack.Item>
                    <Select
                        label="Typography size ratio"
                        options={typoRadiusOptions}
                        onChange={(value) =>
                            handleChangeTypographyField(
                                "typo_size_ratio",
                                value
                            )
                        }
                        value={customTypography?.typo_size_ratio}
                    />
                </LegacyStack.Item>
            </LegacyCard.Section>
        </>
    );
}
