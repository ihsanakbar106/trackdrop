import {
    ActionList,
    Badge,
    BlockStack,
    Box,
    Button,
    ButtonGroup,
    Card,
    ChoiceList,
    DatePicker,
    FormLayout,
    Grid,
    Icon,
    InlineGrid,
    InlineStack,
    Layout,
    LegacyStack,
    OptionList,
    Page,
    Popover,
    Scrollable,
    Select,
    SkeletonBodyText,
    SkeletonDisplayText,
    Text,
    TextField,
    Tooltip,
    useBreakpoints,
} from "@shopify/polaris";
import { ArrowRightIcon, CalendarIcon, ChevronRightIcon } from "@shopify/polaris-icons";
import React, { useCallback, useContext, useEffect, useRef, useState } from "react";
import ReactECharts from "echarts-for-react";
import { AppContext } from "../../components";
import { useAppBridge } from "@shopify/app-bridge-react";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";

export default function TrackingPage() {
    const appBridge = useAppBridge();
    const { apiUrl } = useContext(AppContext);
    const { mdDown, lgUp } = useBreakpoints();
    const shouldShowMultiMonth = lgUp;
    const today = new Date(new Date().setHours(0, 0, 0, 0));
    const yesterday = new Date(new Date(new Date().setDate(today.getDate() - 1)).setHours(0, 0, 0, 0));
    const ranges = [
        {
            title: "Today",
            alias: "today",
            period: {
                since: today,
                until: today,
            },
        },
        {
            title: "Last 7 days",
            alias: "last7days",
            period: {
                since: new Date(new Date(new Date().setDate(today.getDate() - 7)).setHours(0, 0, 0, 0)),
                until: today,
            },
        },
        {
            title: "Last 30 days",
            alias: "last30days",
            period: {
                since: new Date(new Date(new Date().setDate(today.getDate() - 30)).setHours(0, 0, 0, 0)),
                until: today,
            },
        },
        {
            title: "Last 60 days",
            alias: "last60days",
            period: {
                since: new Date(new Date(new Date().setDate(today.getDate() - 60)).setHours(0, 0, 0, 0)),
                until: today,
            },
        },
        {
            title: "Last 90 days",
            alias: "last90days",
            period: {
                since: new Date(new Date(new Date().setDate(today.getDate() - 90)).setHours(0, 0, 0, 0)),
                until: today,
            },
        },
    ];
    const [activeDateRange, setActiveDateRange] = useState(ranges[4]);
    const [inputValues, setInputValues] = useState({});
    const [{ month, year }, setDate] = useState({
        month: activeDateRange.period.since.getMonth(),
        year: activeDateRange.period.since.getFullYear(),
    });
    const datePickerRef = useRef(null);
    const VALID_YYYY_MM_DD_DATE_REGEX = /^\d{4}-\d{1,2}-\d{1,2}/;
    const [popoverActiveOrderDate, setPopoverActiveOrderDate] = useState(false);
    const [popoverActiveCarrier, setPopoverActiveCarrier] = useState(false);
    const [popoverActiveDestination, setPopoverActiveDestination] = useState(false);
    const [selectedCarrier, setSelectedCarrier] = useState([]);
    const [selectedDestination, setSelectedDestination] = useState([]);
    const [loading, setLoading] = useState(true);
    const [toggleData, setToggleData] = useState(true);
    const [carriersOptions, setCarriersOptions] = useState([]);
    const [destinationsOptions, setDestinationsOptions] = useState([]);
    const [totalShipments, setTotalShipments] = useState(0);
    const [deliveryPerformance, setDeliveryPerformance] = useState(0);
    const [deliveryPerformancePercentage, setDeliveryPerformancePercentage] = useState(0);
    const [validTracking, setValidTracking] = useState(0);
    const [validTrackingPercentage, setValidTrackingPercentage] = useState(0);
    const [shipments, setShipments] = useState([]);
    const [pageClick, setPageClick] = useState([]);
    const [pageClickCount, setPageClickCount] = useState(0);
    const [pageClickPer, setPageClickPer] = useState(0);
    const [recommendedProductClickCount, setRecommendedProductClickCount] = useState(0);
    const [recommendedProductClickPer, setRecommendedProductClickPer] = useState(0);
    const [recommendedProductClick, setRecommendedProductClick] = useState([]);
    const [popoverActive, setPopoverActive] = useState(false);

    const fetchData = async () => {
        try {
            const sessionToken = await getSessionToken(appBridge);
            const response = await axios.get(
                `${apiUrl}analytics/tracking_page?shipment_date_datefilter=${activeDateRange?.title}&shipment_date_starting=${activeDateRange?.period?.since}&shipment_date_ending=${activeDateRange?.period?.until}&carrier=${selectedCarrier}&destinations=${selectedDestination}`,
                {
                    headers: {
                        Authorization: `Bearer ${sessionToken}`,
                    },
                },
            );
            const { page_click,total_page_click,recommended_product_click,total_recommended_product_click  } = response?.data;
            const totalPagesClick = page_click.reduce((total, click) => total + Number(click?.click_count), 0);
            const totalRecommendedProductClick= recommended_product_click.reduce((total, click) => total + Number(click?.click_count), 0);
            const page_click_per =total_page_click>0? ((totalPagesClick / total_page_click) * 100).toFixed(2):0;
            const recommended_product_click_per = total_recommended_product_click>0?((totalRecommendedProductClick / total_recommended_product_click) * 100).toFixed(2):0;

            setPageClickCount(totalPagesClick);
            setRecommendedProductClickCount(totalRecommendedProductClick);
            setPageClickPer(0);
            setRecommendedProductClickPer(0);

            if(page_click_per<100){
                setPageClickPer(page_click_per);
            }if(recommended_product_click_per<100){
                setRecommendedProductClickPer(recommended_product_click_per);
            }
            setPageClick(page_click);
            setRecommendedProductClick(recommended_product_click);
        } catch (error) {
            console.error("Error fetching data:", error);
        } finally {
            setLoading(false);
            setToggleData(false);
        }
    };

    useEffect(() => {
        if (toggleData) {
            fetchData();
        }
    }, [toggleData, activeDateRange, selectedCarrier, selectedDestination]);



    function isDate(date) {
        return !isNaN(new Date(date).getDate());
    }
    function isValidYearMonthDayDateString(date) {
        return VALID_YYYY_MM_DD_DATE_REGEX.test(date) && isDate(date);
    }
    function isValidDate(date) {
        return date.length === 10 && isValidYearMonthDayDateString(date);
    }
    function parseYearMonthDayDateString(input) {
        // Date-only strings (e.g. "1970-01-01") are treated as UTC, not local time
        // when using new Date()
        // We need to split year, month, day to pass into new Date() separately
        // to get a localized Date
        const [year, month, day] = input.split("-");
        return new Date(Number(year), Number(month) - 1, Number(day));
    }
    function formatDateToYearMonthDayDateString(date) {
        const year = String(date.getFullYear());
        let month = String(date.getMonth() + 1);
        let day = String(date.getDate());
        if (month.length < 2) {
            month = String(month).padStart(2, "0");
        }
        if (day.length < 2) {
            day = String(day).padStart(2, "0");
        }
        return [year, month, day].join("-");
    }
    function formatDate(date) {
        return formatDateToYearMonthDayDateString(date);
    }
    function nodeContainsDescendant(rootNode, descendant) {
        if (rootNode === descendant) {
            return true;
        }
        let parent = descendant.parentNode;
        while (parent != null) {
            if (parent === rootNode) {
                return true;
            }
            parent = parent.parentNode;
        }
        return false;
    }
    function isNodeWithinPopover(node) {
        return datePickerRef?.current ? nodeContainsDescendant(datePickerRef.current, node) : false;
    }
    function handleStartInputValueChange(value) {
        setInputValues((prevState) => {
            return { ...prevState, since: value };
        });
        console.log("handleStartInputValueChange, validDate", value);
        if (isValidDate(value)) {
            const newSince = parseYearMonthDayDateString(value);
            setActiveDateRange((prevState) => {
                const newPeriod =
                    prevState.period && newSince <= prevState.period.until
                        ? { since: newSince, until: prevState.period.until }
                        : { since: newSince, until: newSince };
                return {
                    ...prevState,
                    period: newPeriod,
                };
            });
        }
    }
    function handleEndInputValueChange(value) {
        setInputValues((prevState) => ({ ...prevState, until: value }));
        if (isValidDate(value)) {
            const newUntil = parseYearMonthDayDateString(value);
            setActiveDateRange((prevState) => {
                const newPeriod =
                    prevState.period && newUntil >= prevState.period.since
                        ? { since: prevState.period.since, until: newUntil }
                        : { since: newUntil, until: newUntil };
                return {
                    ...prevState,
                    period: newPeriod,
                };
            });
        }
    }
    function handleInputBlur({ relatedTarget }) {
        const isRelatedTargetWithinPopover = relatedTarget != null && isNodeWithinPopover(relatedTarget);
        // If focus moves from the TextField to the Popover
        // we don't want to close the popover
        if (isRelatedTargetWithinPopover) {
            return;
        }
        setPopoverActiveOrderDate(false);
    }
    function handleMonthChange(month, year) {
        setDate({ month, year });
    }
    function handleCalendarChange({ start, end }) {
        const newDateRange = ranges.find((range) => {
            return range.period.since.valueOf() === start.valueOf() && range.period.until.valueOf() === end.valueOf();
        }) || {
            alias: "custom",
            title: "Custom",
            period: {
                since: start,
                until: end,
            },
        };
        setActiveDateRange(newDateRange);
    }
    function apply() {
        setPopoverActiveOrderDate(false);
        setToggleData(true);
        setLoading(true);
    }
    function cancel() {
        setPopoverActiveOrderDate(false);
    }
    useEffect(() => {
        if (activeDateRange) {
            setInputValues({
                since: formatDate(activeDateRange.period.since),
                until: formatDate(activeDateRange.period.until),
            });
            function monthDiff(referenceDate, newDate) {
                return newDate.month - referenceDate.month + 12 * (referenceDate.year - newDate.year);
            }
            const monthDifference = monthDiff(
                { year, month },
                {
                    year: activeDateRange.period.until.getFullYear(),
                    month: activeDateRange.period.until.getMonth(),
                },
            );
            if (monthDifference > 1 || monthDifference < 0) {
                setDate({
                    month: activeDateRange.period.until.getMonth(),
                    year: activeDateRange.period.until.getFullYear(),
                });
            }
        }
    }, [activeDateRange]);
    const buttonValue =
        activeDateRange.title === "Custom"
            ? activeDateRange.period.since.toDateString() + " - " + activeDateRange.period.until.toDateString()
            : activeDateRange.title;

    const optionPageClick = {
        title: {
            text: "",
            left: "center",
        },
        legend: {
            data: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            top: "bottom",
            right: "10%",
            textStyle: {
                color: "#666",
                fontSize: 12,
            },
            itemWidth: 20,
            itemHeight: 10,
            itemGap: 5,
            icon: "rect",
        },
        grid: {
            bottom: "15%",
        },
        tooltip: {
            trigger: "axis",
        },
        xAxis: {
            type: "category",
            data: pageClick?.map((item) => item?.date),
        },
        yAxis: {
            type: "value",
        },
        series: [
            {
                data: pageClick?.map((item) => item?.click_count ),
                type: "line", // Change from "line" to "bar"
            },
        ],
    };
    const optionRecommendedProductClick = {
        title: {
            text: "",
            left: "center",
        },
        legend: {
            data: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            top: "bottom",
            right: "10%",
            textStyle: {
                color: "#666",
                fontSize: 12,
            },
            itemWidth: 20,
            itemHeight: 10,
            itemGap: 5,
            icon: "rect",
        },
        grid: {
            bottom: "15%",
        },
        tooltip: {
            trigger: "axis",
        },
        xAxis: {
            type: "category",
            data: recommendedProductClick?.map((item) => item?.date),
        },
        yAxis: {
            type: "value",
        },
        series: [
            {
                data: recommendedProductClick?.map((item) => item?.click_count ),
                type: "line", // Change from "line" to "bar"
            },
        ],
    };


    const togglePopoverActive = useCallback(() => setPopoverActive((popoverActive) => !popoverActive), []);
    return (

            <Layout>
                <Layout.Section variant="fullWidth">
                    <BlockStack gap={"400"}>
                        <ButtonGroup>
                            <Popover
                                active={popoverActiveOrderDate}
                                autofocusTarget="none"
                                preferredAlignment="left"
                                preferredPosition="below"
                                fluidContent
                                sectioned={false}
                                fullHeight
                                activator={
                                    <Button size="slim" icon={CalendarIcon} onClick={() => setPopoverActiveOrderDate(!popoverActiveOrderDate)} disclosure="select">
                                        {buttonValue}
                                    </Button>
                                }
                                onClose={() => setPopoverActiveOrderDate(false)}
                            >
                                <Popover.Pane >
                                    <InlineGrid
                                        columns={{
                                            xs: "1fr",
                                            mdDown: "1fr",
                                            md: "max-content max-content",
                                        }}
                                        gap={0}
                                        ref={datePickerRef}
                                    >
                                        <Box
                                            maxWidth={mdDown ? "516px" : "212px"}
                                            width={mdDown ? "100%" : "212px"}
                                            padding={{ xs: 500, md: 0 }}
                                            paddingBlockEnd={{ xs: 100, md: 0 }}
                                        >
                                            {mdDown ? (
                                                <Select
                                                    label="dateRangeLabel"
                                                    labelHidden
                                                    onChange={(value) => {
                                                        const result = ranges.find(({ title, alias }) => title === value || alias === value);
                                                        setActiveDateRange(result);
                                                    }}
                                                    value={activeDateRange?.title || activeDateRange?.alias || ""}
                                                    options={ranges.map(({ alias, title }) => title || alias)}
                                                />
                                            ) : (
                                                // <Scrollable style={{ height: "334px" }}>
                                                <OptionList
                                                    options={ranges.map((range) => ({
                                                        value: range.alias,
                                                        label: range.title,
                                                    }))}
                                                    selected={activeDateRange.alias}
                                                    onChange={(value) => {
                                                        setActiveDateRange(ranges.find((range) => range.alias === value[0]));
                                                    }}
                                                />
                                                // </Scrollable>
                                            )}
                                        </Box>
                                        <Box padding={{ xs: 500 }} maxWidth={mdDown ? "320px" : "516px"}>
                                            <BlockStack gap="400">
                                                <InlineStack gap="200" wrap={false}>
                                                    <div style={{ flexGrow: 1 }}>
                                                        <TextField
                                                            role="combobox"
                                                            label={"Since"}
                                                            labelHidden
                                                            prefix={<Icon source={CalendarIcon} />}
                                                            value={inputValues.since}
                                                            onChange={handleStartInputValueChange}
                                                            onBlur={handleInputBlur}
                                                            placeholder="YYYY-MM-DD"
                                                            autoComplete="off"
                                                        />
                                                    </div>
                                                    <Icon source={ArrowRightIcon} />
                                                    <div style={{ flexGrow: 1 }}>
                                                        <TextField
                                                            role="combobox"
                                                            label={"Until"}
                                                            labelHidden
                                                            prefix={<Icon source={CalendarIcon} />}
                                                            value={inputValues.until}
                                                            onChange={handleEndInputValueChange}
                                                            onBlur={handleInputBlur}
                                                            placeholder="YYYY-MM-DD"
                                                            autoComplete="off"
                                                        />
                                                    </div>
                                                </InlineStack>
                                                <div>
                                                    <DatePicker
                                                        month={month}
                                                        year={year}
                                                        selected={{
                                                            start: activeDateRange.period.since,
                                                            end: activeDateRange.period.until,
                                                        }}
                                                        onMonthChange={handleMonthChange}
                                                        onChange={handleCalendarChange}
                                                        multiMonth={shouldShowMultiMonth}
                                                        allowRange
                                                    />
                                                </div>
                                            </BlockStack>
                                        </Box>
                                    </InlineGrid>
                                </Popover.Pane>
                                <Popover.Pane fixed>
                                    <Popover.Section>
                                        <InlineStack align="end">
                                            <ButtonGroup>
                                                <Button onClick={cancel}>Cancel</Button>
                                                <Button variant="primary" onClick={apply}>
                                                    Apply
                                                </Button>
                                            </ButtonGroup>
                                        </InlineStack>
                                    </Popover.Section>
                                </Popover.Pane>
                            </Popover>
                        </ButtonGroup>
                        <Grid gap={{ xs: "1rem", sm: "1rem", md: "1rem", lg: "1rem", xl: "1rem" }}>
                            <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 6, lg: 6, xl: 6 }}>
                                <Card>
                                    <LegacyStack alignment="center">
                                        <LegacyStack.Item fill>
                                            {loading ? (
                                                <SkeletonDisplayText />
                                            ) : (
                                                <Text fontWeight="semibold" variant="headingMd" as="span">
                                                    Page view
                                                </Text>
                                            )}
                                            <div className="mt-3">
                                                {loading ? (
                                                    <SkeletonBodyText lines={1} />
                                                ) : (
                                                    <InlineStack gap={"100"}>
                                                        <Text as="span" variant="headingLg">
                                                            {pageClickCount}
                                                        </Text>
                                                        {pageClickPer > 0 && (
                                                            <Text variant="bodyMd" fontWeight="semibold" tone="subdued">
                                                                ({pageClickPer}%)
                                                            </Text>
                                                        )}
                                                    </InlineStack>
                                                )}
                                            </div>
                                        </LegacyStack.Item>
                                    </LegacyStack>
                                </Card>
                            </Grid.Cell>
                            <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 6, lg: 6, xl: 6 }}>
                                <Card>
                                    <LegacyStack alignment="center">
                                        <LegacyStack.Item fill>
                                            {loading ? (
                                                <SkeletonDisplayText />
                                            ) : (
                                                <Text fontWeight="semibold" variant="headingMd" as="span">
                                                    Product recommendation clicks
                                                </Text>
                                            )}
                                            <div className="mt-3">
                                                {loading ? (
                                                    <SkeletonBodyText lines={1} />
                                                ) : (
                                                    <InlineStack gap={"100"}>
                                                        <Text as="span" variant="headingLg">
                                                            {recommendedProductClickCount}
                                                        </Text>
                                                        {recommendedProductClickPer > 0 && (
                                                            <Text variant="bodyMd" fontWeight="semibold" tone="subdued">
                                                                ({recommendedProductClickPer}%)
                                                            </Text>
                                                        )}
                                                    </InlineStack>
                                                )}
                                            </div>
                                        </LegacyStack.Item>
                                    </LegacyStack>
                                </Card>
                            </Grid.Cell>
                        </Grid>
                        <InlineGrid columns={2} gap={"100"} >
                        <Card>
                            <BlockStack gap={"400"}>
                                {loading ? (
                                    <SkeletonDisplayText />
                                ) : (
                                    <Text as="h2" variant="headingSm">
                                        Page view
                                    </Text>
                                )}
                                {loading ? (
                                    <SkeletonBodyText lines={15} />
                                ) : (
                                    <ReactECharts className="._CardSection__ReactECharts_15o35_3" option={optionPageClick} style={{ height: "400px", width: "100%" }} />
                                )}
                            </BlockStack>
                        </Card>
                        <Card>
                            <BlockStack gap={"400"}>
                                {loading ? (
                                    <SkeletonDisplayText />
                                ) : (
                                    <Text as="h2" variant="headingSm">
                                        Product recommendation clicks
                                    </Text>
                                )}
                                {loading ? (
                                    <SkeletonBodyText lines={15} />
                                ) : (
                                    <ReactECharts className="._CardSection__ReactECharts_15o35_3" option={optionRecommendedProductClick} style={{ height: "400px", width: "100%" }} />
                                )}
                            </BlockStack>
                        </Card>
                        </InlineGrid>
                    </BlockStack>
                </Layout.Section>
                <Layout.Section></Layout.Section>
                <Layout.Section></Layout.Section>
            </Layout>
    );
}
