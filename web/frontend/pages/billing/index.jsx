import { Toast, useAppBridge, useNavigate } from "@shopify/app-bridge-react";
import {
    Badge,Banner,
    Button,ProgressBar,
    Card,Select,Divider,
    ChoiceList,
    Icon,
    IndexFilters,
    InlineGrid,
    IndexTable,
    InlineStack,
    Layout,
    LegacyCard,
    LegacyStack,
    Link,
    Page,
    RangeSlider,
    Text,
    TextField,
    Tooltip,
    BlockStack,
    FormLayout,
    useBreakpoints,
    useIndexResourceState,
    useSetIndexFiltersMode,
    Thumbnail,
} from "@shopify/polaris";
import {
    FormsIcon,
    ViewIcon,
    EmailIcon,
    ChatIcon,
    SettingsIcon,
    PersonIcon,
} from "@shopify/polaris-icons";
import React, { useCallback, useContext, useEffect, useState } from "react";
import { AppContext } from "../../components";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import { SkeltonPage, SkeltonPageForTable } from "../../components/SkeltonPage";
import TableSkeletonWithTabs from "../../components/TableSkeletonWithTabs";
import { useTranslation } from "react-i18next";

export default function PageName() {
    const { t } = useTranslation();
    const { apiUrl, shop } = useContext(AppContext);
    const appBridge = useAppBridge();
    const [loading, setLoading] = useState(true);
    const [btnLoading, setBtnLoading] = useState(false);
    const [toastMsg, setToastMsg] = useState("");
    const [errorToast, setErrorToast] = useState(false);
    const [successToast, setSuccessToast] = useState(false);
    const [planUsage, setPlanUsage] = useState(0);
    const [starterPlans, setStarterPlans] = useState([]);
    const [selectedStarterPlan, setSelectedStarterPlan] = useState({});
    const [growthPlans, setGrowthPlans] = useState([]);
    const [selectedGrowthPlan, setSelectedGrowthPlan] = useState({});
    const [advancePlans, setAdvancePlans] = useState([]);
    const [selectedAdvancePlan, setSelectedAdvancePlan] = useState({});

    const [subscriptionPlans, setSubscriptionPlans] = useState([]);
    const [currentPlan, setCurrentPlan] = useState([]);
    const [interval, setInterval] = useState('');

    const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
    const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

    const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

    const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

    const fetchData = async () => {
        try {
            const sessionToken = await getSessionToken(appBridge);
            const headers = {
                Authorization: `Bearer ${sessionToken}`,
            };
            const response = await axios.get(
                `${apiUrl}all_plans`, {headers},
            );
            const { starter_plans,growth_plans,advance_plans,active_plan,total_req} = response?.data;
            setCurrentPlan(active_plan);
            setStarterPlans(starter_plans);
            setGrowthPlans(growth_plans);
            setAdvancePlans(advance_plans);
            setSelectedStarterPlan(starter_plans[0]);
            setSelectedGrowthPlan(growth_plans[0]);
            setSelectedAdvancePlan(advance_plans[0]);
            setPlanUsage(total_req);
            if(active_plan){
                if(active_plan.category=="Starter"){
                    setSelectedStarterPlan(active_plan);
                }else if(active_plan.category=="Growth"){
                    setSelectedGrowthPlan(active_plan);
                }else if(active_plan.category=="Advanced"){
                    setSelectedAdvancePlan(active_plan);

                }
            }

            // setSubscriptionPlans(all_plans);

        } catch (error) {
            console.error("Error fetching data:", error);
        } finally {
            setLoading(false);
        }
    };
    const handlePlanSubscription = async (id) => {
        let sessionToken = await getSessionToken(appBridge);
        setBtnLoading(id);
        // console.log('plan id',id);
        try {
            const response = await axios.get(`${apiUrl}active_plan?plan_id=${id}`, {
                headers: {
                    Authorization: `Bearer ${sessionToken}`,
                },
            });


            if (response.data?.status=="success") {
                if (response.data.confirmation_url) {
                    window.parent.location.href = response.data.confirmation_url;
                    // window.parent.open(response.data.confirmation_url,"_self");
                } else if (response.data.free_shop) {
                    setToastMsg(response.data.message || "Free access enabled. No charge applied.");
                    setSuccessToast(true);
                    fetchData();
                }
            }
        } catch (err) {
            console.warn(err);
            throw err;
        } finally {
            setBtnLoading(null);
        }
    };

    const handleCancelPlanSubscription = async (id) => {
        let sessionToken = await getSessionToken(appBridge);
        setCancelBtnLoading(true);
        try {
            const response = await axios.get(`${apiUrl}cancel-plan-subscription?shop=${shop}&plan_id=${id}`, {
                headers: {
                    Authorization: `Bearer ${sessionToken}`,
                },
            });
            if (response.data?.success) {
                CurrentPlan();
                FetchPlans();
                setIsSubscribed(false)
            } else {
                show(response.data?.message, { isError: true, duration: 2000 });
            }
        } catch (err) {
            console.warn(err);
            throw err;
        } finally {
            setCancelBtnLoading(false)
            setBtnLoading(null);
        }
    };
    const optionList = [
        { label: 'Select Plan', value: '',disabled:true},
        { label: 'Monthly', value: 'monthly' },
        { label: 'Annually', value: 'annually' },

    ];
    const handleChangeValue = ( value) => {
        // setType(prevType => ({
        //     ...prevType,
        //     [planId]: value
        // }));
        setInterval(value)
    };
    const handleChangeAdvanceValue = (value) => {
        const selectedPlan = advancePlans.find((plan) => plan.id === parseInt(value)); // Convert value to number
        setSelectedAdvancePlan(selectedPlan); // Set the full plan data
    };
    const handleChangeGrowthValue = (value) => {
        const selectedPlan = growthPlans.find((plan) => plan.id === parseInt(value)); // Convert value to number
        setSelectedGrowthPlan(selectedPlan); // Set the full plan data
    };
    useEffect(() => {
        fetchData();
    }, []);

    return loading ? (
        <TableSkeletonWithTabs
            primaryAction={false}
            length="5"
            fullWidth={false}
            SkeletonTabsLeft={false}
            thumbnail={false}
            checkbox={false}
            SkeletonTabsLeftLength={0}
        />
    ) : (
        <Page title={"Billing"}>
            <Layout>
                <Layout.Section>
                    {currentPlan?.name ?
                    <Card>
                        <Text variant="headingLg" as="h2"  fontWeight="semibold">
                            {currentPlan?.name}
                        </Text>
                        <Text variant="bodyMd" as="p">
                            Available/Total
                        </Text>
                        <Text variant="bodyMd" as="p">
                           <b>{(currentPlan?.response_limit-planUsage)}</b>/{currentPlan?.response_limit}
                        </Text>
                        <ProgressBar size={"small"} tone={"primary"} progress={currentPlan?.response_limit ? ((currentPlan?.response_limit-planUsage) / currentPlan.response_limit) * 100 : 0} />
                    </Card>
                        :
                        <Banner tone="warning" title="Please select a billing plan to gain full access to the app’s features and dedicated support."></Banner>
                    }
                </Layout.Section>
            </Layout>
<br/>
            <Layout>
                <Layout.Section>
                    <Card padding={0}>
                    <div className="{/*grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-0*/}">
                        {/*<div key={selectedStarterPlan?.id} style={{border:"1px solid #ebebeb"}} className="">
                            <div
                                className={` bg-white h-full grid  relative`}

                            >
                                <div className="my-6 mb-8 mx-5 z-0">
                                    <BlockStack gap="150" align="center" >
                                        <Text variant="headingLg" as="h2"  fontWeight="semibold">
                                            {selectedStarterPlan?.name}
                                        </Text>
                                        <div>
                                        <Text as="p"  tone={'subdued'} fontWeight={'regular'} variant="bodyMd">
                                            Perfect for new business
                                        </Text>
                                        <div>&nbsp;</div>
                                        </div>

                                        <InlineStack gap="100" blockAlign="center">
                                            <Text variant="heading2xl" as="h2" fontWeight="semibold">
                                                {
                                                    (selectedStarterPlan?.price != 0)?selectedStarterPlan?.price:"Free"

                                                }
                                            </Text>
                                            {
                                                selectedStarterPlan?.price != 0 &&
                                                <Text as="p" variant="bodyLg">
                                                    /month
                                                </Text>
                                            }
                                        </InlineStack>
                                        <Divider borderWidth={'2'} />
                                    </BlockStack>
                                    <BlockStack gap="100" align="center">
                                        <div className="w-full"  style={{marginTop: "20px"}}>
                                            <Text variant="bodyMd" as="h2" >Track shipments per month</Text>
                                            <Text variant="bodyMd" as="p" fontWeight="semibold">{selectedStarterPlan?.response_limit}</Text>
                                            <br/>
                                            <Text variant="bodyMd" as="p" tone={'subdued'} >Unavailable after quota is exceeded</Text>
                                        </div>
                                        <div style={{display: "flex", gap: "1rem", marginTop: "10px"}}>
                                            <Button
                                                // tone={'success'}
                                                size="large"
                                                fullWidth
                                                variant="primary"
                                                key={selectedStarterPlan?.id}
                                                loading={btnLoading === selectedStarterPlan?.id}
                                                disabled={selectedStarterPlan?.id === currentPlan?.id}
                                                onClick={() => handlePlanSubscription(selectedStarterPlan?.id)}

                                            >
                                                {(() => {
                                                    // Default button text
                                                    let buttonText = "Select Plan";

                                                    // Check current plan status and set button text accordingly
                                                    if (currentPlan?.id) {
                                                        if (selectedStarterPlan?.id === currentPlan?.id) {
                                                            buttonText = "Current Plan";
                                                        } else {
                                                            buttonText = "Upgrade";
                                                        }
                                                    }
                                                    return buttonText;
                                                })()}
                                            </Button>
                                        </div>
                                        <div style={{marginTop: "10px"}}>
                                            <div className="w-fit">
                                                <div className="post__content" dangerouslySetInnerHTML={{__html: selectedStarterPlan?.terms}}></div>

                                            </div>
                                            <div className="w-fit">
                                                <InlineStack gap="200" blockAlign="center" wrap={false}>
                                                    <Icon source={ChatIcon} size="medium" />
                                                    <Text as="p" variant="bodyMd">
                                                        24/7 Customer Support
                                                    </Text>
                                                </InlineStack>
                                            </div>
                                        </div>
                                    </BlockStack>
                                </div>
                            </div>
                        </div>*/}
                        <div key={selectedGrowthPlan?.id}
                             // style={{border:"1px solid #ebebeb"}}
                             className="">
                            <div
                                className={` bg-white h-full grid  relative`}
                            >
                                <div className="my-6 mb-8 mx-5 z-0">
                                    <BlockStack gap="150" align="center" >
                                        <Text variant="headingLg" as="h2"  fontWeight="semibold">
                                            {selectedGrowthPlan?.name}
                                        </Text>
                                        <div>
                                            <Text as="p"  tone={'subdued'} fontWeight={'regular'} variant="bodyMd">
                                                Advanced tools for growing brands
                                            </Text>
                                            <div>&nbsp;</div>
                                        </div>
                                        <InlineStack gap="100" blockAlign="center">
                                            <Text variant="heading2xl" as="h2" fontWeight="semibold">
                                                {
                                                    (selectedGrowthPlan?.price != 0)?"$ "+selectedGrowthPlan?.price:"Free"

                                                }
                                            </Text>
                                            {
                                                selectedGrowthPlan?.price != 0 &&
                                                <Text as="p" variant="bodyLg">
                                                    /month
                                                </Text>
                                            }
                                        </InlineStack>
                                        <Divider borderWidth={'2'} />
                                    </BlockStack>
                                    <BlockStack gap="100" align="center">
                                        <div className="w-full"  style={{marginTop: "20px"}}>
                                            <Select
                                                label={"Track shipments per month"}
                                                options={growthPlans.map((plan) => ({
                                                    label: `${plan.response_limit}`,  // Use the plan's name and price
                                                    value: plan.id // Ensure the value is a string
                                                }))}
                                                onChange={(value) => handleChangeGrowthValue(value)}
                                                // value={type[data?.id]}
                                                value={selectedGrowthPlan?.id}
                                                helpText={`$${selectedGrowthPlan?.usage_charges} per ${selectedGrowthPlan?.unlimited==1?"":"extra "} shipment tracking`}
                                            />
                                            {/*<div>&nbsp;</div>*/}
                                        </div>
                                        <div className="w-fit">
                                            <Text tone={"subdued"}> Trial Days : {selectedGrowthPlan?.trial_days}</Text>
                                        </div>
                                        <div style={{display: "flex", gap: "1rem", marginTop: "10px"}}>
                                            <Button
                                                // tone={'success'}
                                                size="large"
                                                fullWidth
                                                variant="primary"
                                                key={selectedGrowthPlan?.id}
                                                loading={btnLoading === selectedGrowthPlan?.id}
                                                disabled={selectedGrowthPlan?.id === currentPlan?.id}
                                                onClick={() => handlePlanSubscription(selectedGrowthPlan?.id)}

                                            >
                                                {(() => {
                                                    // Default button text
                                                    let buttonText = "Select Plan";

                                                    // Check current plan status and set button text accordingly
                                                    if (currentPlan?.id) {
                                                        if (selectedGrowthPlan?.id === currentPlan?.id) {
                                                            buttonText = "Current Plan";
                                                        } else {
                                                            buttonText = "Upgrade";
                                                        }
                                                    }
                                                    return buttonText;
                                                })()}
                                            </Button>
                                        </div>

                                        <div style={{marginTop: "10px"}}>
                                            <div className="w-fit">
                                                <div className="post__content" dangerouslySetInnerHTML={{__html: selectedGrowthPlan?.terms}}></div>
                                            </div>
                                            {/*<div className="w-fit">
                                                <InlineStack gap="200" blockAlign="center" wrap={false}>
                                                    <Icon source={ChatIcon} size="medium" />
                                                    <Text as="p" variant="bodyMd">
                                                        24/7 Customer Support
                                                    </Text>
                                                </InlineStack>
                                            </div>*/}
                                        </div>
                                    </BlockStack>
                                </div>
                            </div>
                        </div>
                        {/*<div key={selectedAdvancePlan?.id} style={{border:"1px solid #ebebeb"}} className="">
                            <div
                                className={` bg-white h-full grid  relative`}
                                // style={{boxShadow: "0 0px 1.5px gray"}}
                            >
                                <div className="my-6 mb-8 mx-5 z-0">
                                    <BlockStack gap="150" align="center" >
                                        <Text variant="headingLg" as="h2"  fontWeight="semibold">
                                            {selectedAdvancePlan?.name}
                                        </Text>
                                        <Text as="p"  tone={'subdued'} fontWeight={'regular'} variant="bodyMd">
                                            {selectedAdvancePlan?.terms}
                                        </Text>
                                        <InlineStack gap="100" blockAlign="center">
                                            <Text variant="heading2xl" as="h2" fontWeight="semibold">
                                                {
                                                    (selectedAdvancePlan?.price != 0)?"$ "+selectedAdvancePlan?.price:"Free"

                                                }
                                            </Text>
                                            {
                                                selectedAdvancePlan?.price != 0 &&
                                                <Text as="p" variant="bodyLg">
                                                    /month
                                                </Text>
                                            }
                                        </InlineStack>
                                        <Divider borderWidth={'2'} />
                                    </BlockStack>
                                    <BlockStack gap="100" align="center">
                                        <div className="w-full"  style={{marginTop: "20px"}}>
                                            <Select
                                                label={"Orders per month"}
                                                options={advancePlans.map((plan) => ({
                                                    label: `${plan.response_limit}`,  // Use the plan's name and price
                                                    value: plan.id // Ensure the value is a string
                                                }))}
                                                onChange={(value) => handleChangeAdvanceValue(value)}
                                                // value={type[data?.id]}
                                                value={selectedAdvancePlan?.id}
                                                helpText="$0.05 per extra order"

                                            />
                                            <div>&nbsp;</div>
                                        </div>

                                        <div style={{display: "flex", gap: "1rem", marginTop: "10px"}}>
                                            <Button
                                                // tone={'success'}
                                                size="large"
                                                fullWidth
                                                variant="primary"
                                                key={selectedAdvancePlan?.id}
                                                loading={btnLoading === selectedAdvancePlan?.id}
                                                disabled={selectedAdvancePlan?.id === currentPlan?.id}
                                                onClick={() => handlePlanSubscription(selectedAdvancePlan?.id)}

                                            >
                                                {(() => {
                                                    // Default button text
                                                    let buttonText = "Select Plan";

                                                    // Check current plan status and set button text accordingly
                                                    if (currentPlan?.id) {
                                                        if (selectedAdvancePlan?.id === currentPlan?.id) {
                                                            buttonText = "Current Plan";
                                                        } else {
                                                            buttonText = "Upgrade";
                                                        }
                                                    }
                                                    return buttonText;
                                                })()}
                                            </Button>
                                        </div>

                                        <div style={{marginTop: "10px"}}>
                                            <div className="w-fit">
                                                <InlineStack gap="200" blockAlign="center" wrap={false}>
                                                    <Icon source={FormsIcon} size="medium" />
                                                    <Text as="p" variant="bodyMd">
                                                        <b>
                                                            {selectedAdvancePlan?.response_limit}
                                                        </b>
                                                        &nbsp;Orders per month
                                                    </Text>
                                                </InlineStack>
                                            </div>
                                            <div className="w-fit">
                                                <InlineStack gap="200" blockAlign="center" wrap={false}>
                                                    <Icon source={ChatIcon} size="medium" />
                                                    <Text as="p" variant="bodyMd">
                                                        24/7 Customer Support
                                                    </Text>
                                                </InlineStack>
                                            </div>
                                        </div>
                                    </BlockStack>
                                </div>
                            </div>
                        </div>*/}
                        {/*<div key={"custom-1"} style={{border:"1px solid #ebebeb"}} className="">
                            <div
                                className={` bg-white h-full grid  relative`}
                                // style={{boxShadow: "0 0px 1.5px gray",borderRadius:"0px 10px 10px 0px"}}
                            >
                                <div className="my-6 mb-8 mx-5 z-0">
                                    <BlockStack gap="150" align="center" >
                                        <Text variant="headingLg" as="h2"  fontWeight="semibold">
                                            Enterprise
                                        </Text>
                                        <div>
                                            <Text as="p"  tone={'subdued'} fontWeight={'regular'} variant="bodyMd">
                                                Customization + Priority Support
                                            </Text>
                                            <div>&nbsp;</div>
                                        </div>

                                        <InlineStack gap="100" blockAlign="center">
                                            <Text variant="heading2xl" as="h2" fontWeight="semibold">
                                                Custom
                                            </Text>
                                        </InlineStack>
                                        <Divider borderWidth={'2'} />
                                    </BlockStack>
                                    <BlockStack gap="100" align="center">
                                        <div className="w-full"  style={{marginTop: "20px"}}>
                                            <Text variant="bodyMd" as="h2" >Orders per month</Text>
                                            <Text variant="bodyMd" as="p" fontWeight="semibold">50,000+</Text>
                                            <div>&nbsp;</div>
                                            <Text variant="bodyMd" as="p" tone={'subdued'} >$0.05 per extra order</Text>
                                            <div>&nbsp;</div>
                                        </div>

                                        <div style={{display: "flex", gap: "1rem", marginTop: "10px"}}>
                                            <Button
                                                // tone={'success'}
                                                size="large"
                                                fullWidth

                                                onClick={() => {}}

                                            >
                                                Contact us
                                            </Button>
                                        </div>


                                    </BlockStack>
                                </div>
                            </div>
                        </div>*/}
                    </div>
                    </Card>
                </Layout.Section>
            </Layout>
            <Layout>
                <Layout.Section>
                    <br/>
                </Layout.Section>
            </Layout>
            {toastErrorMsg}
            {toastSuccessMsg}
        </Page>
    );
}
