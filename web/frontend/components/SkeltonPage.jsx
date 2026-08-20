import {
    SkeletonPage,
    Layout,
    LegacyCard,
    SkeletonBodyText,
    TextContainer,
    SkeletonDisplayText,
    SkeletonTabs,
    SkeletonThumbnail,
    Button,
    LegacyStack,
} from "@shopify/polaris";
import React from "react";

// import { PaymentLoader } from '../../components'

export function SkeltonPage() {
    return (
        <SkeletonPage primaryAction fullWidth>
            <Layout>
                <Layout.Section secondary>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>
                    </LegacyCard>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>
                    </LegacyCard>
                </Layout.Section>

                <Layout.Section>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>
                    </LegacyCard>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>
                    </LegacyCard>
                </Layout.Section>
            </Layout>
        </SkeletonPage>
    );
}

export function SkeltonDashboardPage() {
    return (
        <SkeletonPage primaryAction fullWidth>
            <LegacyCard>
                <SkeletonTabs count={4} />
                <LegacyCard.Section sectioned>
                    <TextContainer>
                        <SkeletonBodyText lines={4} />
                    </TextContainer>
                </LegacyCard.Section>
            </LegacyCard>
            {/* <br />
            <br />
            <br />
            <PaymentLoader /> */}
        </SkeletonPage>
    );
}

export function SkeltonPageWithTabs() {
    return (
        <SkeletonPage primaryAction fullWidth>
            <LegacyCard sectioned>
                <SkeletonTabs count={5} />

                <Layout>
                    <Layout.Section secondary>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>

                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>
                    </Layout.Section>

                    <Layout.Section>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>

                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>
                    </Layout.Section>
                </Layout>
            </LegacyCard>
        </SkeletonPage>
    );
}

export function SkeltonSimpleTextLines() {
    return (
        <>
            <Layout>
                <Layout.Section>
                    <div
                        style={{ paddingRight: "20px", paddingBottom: "20px" }}
                    >
                        <SkeletonBodyText />
                        <SkeletonBodyText />
                    </div>
                </Layout.Section>
            </Layout>
        </>
    );
}

export function SkeltonTabsLayoutSecondary() {
    return (
        <div className="SkeltonTabs">
            <LegacyCard sectioned>
                <Layout>
                    <Layout.Section secondary>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>
                    </Layout.Section>

                    <Layout.Section>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText lines={6} />
                        </TextContainer>
                    </Layout.Section>
                </Layout>
            </LegacyCard>
        </div>
    );
}

export function SkeltonTabsLayoutFull() {
    return (
        <div className="SkeltonTabs">
            <LegacyCard sectioned>
                <Layout>
                    <Layout.Section>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText lines={3} />
                        </TextContainer>

                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText lines={3} />
                        </TextContainer>
                    </Layout.Section>
                </Layout>
            </LegacyCard>
        </div>
    );
}

export function SkeltonTabsWithThumbnail() {
    return (
        <div className="SkeltonTabs">
            <LegacyCard sectioned>
                <Layout>
                    <Layout.Section>
                        <LegacyStack>
                            <SkeletonThumbnail size="small" />
                            <SkeletonBodyText lines={2} />
                        </LegacyStack>
                    </Layout.Section>
                </Layout>
            </LegacyCard>

            <LegacyCard sectioned>
                <Layout>
                    <Layout.Section>
                        <LegacyStack>
                            <SkeletonThumbnail size="small" />
                            <SkeletonBodyText lines={2} />
                        </LegacyStack>
                    </Layout.Section>
                </Layout>
            </LegacyCard>
        </div>
    );
}

export function SkeltonPageForTable() {
    return (
        <SkeletonPage primaryAction fullWidth>
            <LegacyCard sectioned>
                <Layout>
                    <Layout.Section oneThird>
                        <TextContainer>
                            <SkeletonBodyText lines={4} />
                        </TextContainer>
                    </Layout.Section>

                    <Layout.Section oneThird>
                        <TextContainer>
                            <SkeletonBodyText lines={4} />
                        </TextContainer>
                    </Layout.Section>

                    <Layout.Section oneThird>
                        <TextContainer>
                            <SkeletonBodyText lines={4} />
                        </TextContainer>
                    </Layout.Section>
                </Layout>
            </LegacyCard>
        </SkeletonPage>
    );
}

export function SkeltonProductGrid() {
    return (
        <>
            <Layout>
                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>

                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>

                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>

                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>
            </Layout>
            <br />
            <Layout>
                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>

                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>

                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>

                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>
            </Layout>
            <br />
            <Layout>
                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>

                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>

                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>

                <Layout.Section oneThird>
                    <TextContainer>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={4} />
                        </LegacyCard>
                    </TextContainer>
                </Layout.Section>
            </Layout>
        </>
    );
}

export function SkeltonPageForProductDetail() {
    return (
        <SkeletonPage primaryAction fullWidth>
            <Layout>
                <Layout.Section>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonBodyText />
                        </TextContainer>
                    </LegacyCard>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonBodyText />
                        </TextContainer>
                    </LegacyCard>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonBodyText />
                        </TextContainer>
                    </LegacyCard>
                </Layout.Section>

                <Layout.Section secondary>
                    <LegacyCard>
                        <LegacyCard.Section>
                            <SkeletonBodyText lines={2} />
                        </LegacyCard.Section>
                        <LegacyCard.Section>
                            <SkeletonBodyText lines={2} />
                        </LegacyCard.Section>
                    </LegacyCard>

                    <LegacyCard subdued>
                        <LegacyCard.Section>
                            <SkeletonBodyText lines={2} />
                        </LegacyCard.Section>
                        <LegacyCard.Section>
                            <SkeletonBodyText lines={2} />
                        </LegacyCard.Section>
                    </LegacyCard>
                </Layout.Section>
            </Layout>
        </SkeletonPage>
    );
}

export function SkeltonPaymentPage() {
    return (
        // <SkeletonPage primaryAction fullWidth>
        //     <SkeletonTabs />
        <div
            className="Polaris-Page--fullWidth Polaris-SkeletonPage__Page "
            style={{ maxWidth: "none", padding: "0" }}
        >
            <Layout>
                <Layout.Section oneThird>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText lines={4} />
                        </TextContainer>
                    </LegacyCard>
                </Layout.Section>

                <Layout.Section oneThird>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText lines={4} />
                        </TextContainer>
                    </LegacyCard>
                </Layout.Section>

                <Layout.Section oneThird>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText lines={4} />
                        </TextContainer>
                    </LegacyCard>
                </Layout.Section>
            </Layout>
        </div>
        // </SkeletonPage>
    );
}

export function SkeltonShippingPage() {
    return (
        <SkeletonPage primaryAction fullWidth>
            <SkeletonTabs count={2} />
            <br />
            <LegacyCard sectioned>
                <Layout>
                    <Layout.Section oneThird>
                        <TextContainer>
                            <SkeletonBodyText lines={4} />
                        </TextContainer>
                    </Layout.Section>

                    <Layout.Section oneThird>
                        <TextContainer>
                            <SkeletonBodyText lines={4} />
                        </TextContainer>
                    </Layout.Section>

                    <Layout.Section oneThird>
                        <TextContainer>
                            <SkeletonBodyText lines={4} />
                        </TextContainer>
                    </Layout.Section>
                </Layout>
            </LegacyCard>
        </SkeletonPage>
    );
}
