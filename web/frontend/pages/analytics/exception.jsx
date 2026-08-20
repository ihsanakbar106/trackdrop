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

export default function ExceptionPage() {
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
    const [validTracking, setValidTracking] = useState(0);
    const [validTrackingPercentage, setValidTrackingPercentage] = useState(0);
    const [totalExceptions, setTotalExceptions] = useState(0);
    const [exceptionsPercentage, setExceptionsPercentage] = useState(0);
    const [shipments, setShipments] = useState([]);
    const [shipmentsByCarrier, setShipmentsByCarrier] = useState([]);
    const [shipmentStatus, setShipmentStatus] = useState([]);

    const [exception, setException] = useState({ count: 0, percentage: 0 });
    const [unclaimed, setUnclaimed] = useState({ count: 0, percentage: 0 });
    const [retainedCustom, setRetainedCustom] = useState({ count: 0, percentage: 0 });
    const [shipmentDamage, setShipmentDamage] = useState({ count: 0, percentage: 0 });
    const [shipmentCancel, setShipmentCancel] = useState({ count: 0, percentage: 0 });
    const [refusedCustomer, setRefusedCustomer] = useState({ count: 0, percentage: 0 });
    const [returnSender, setReturnSender] = useState({ count: 0, percentage: 0 });
    const [returningSender, setReturningSender] = useState({ count: 0, percentage: 0 });

    const [popoverActive, setPopoverActive] = useState(false);
    const [selected, setSelected] = useState(["Shipments"]);
    const [chartSize, setChartSize] = useState({
        width: '100%',
        height: '400px',
        // legendOrient: 'vertical', // Default legend orientation
        legendOrient: 'horizontal', // Default legend orientation
        // legendPosition: { left: 'center', top: 'bottom' }, // Default legend position
        legendPosition: { left: 'right', top: 'center' }, // Default legend position
        chartRadius: ['40%', '70%'], // No extra padding on desktop
    });
    const fetchData = async () => {
        try {
            const sessionToken = await getSessionToken(appBridge);
            const response = await axios.get(
                `${apiUrl}analytics/exception_page?shipment_date_datefilter=${activeDateRange?.title}&shipment_date_starting=${activeDateRange?.period?.since}&shipment_date_ending=${activeDateRange?.period?.until}&carrier=${selectedCarrier}&destinations=${selectedDestination}`,
                {
                    headers: {
                        Authorization: `Bearer ${sessionToken}`,
                    },
                },
            );
            const {total_shipments, shipments, carriers, destinations, shipment_statuses,shipments_by_carrier } = response?.data;
            const totalExceptions = shipment_statuses.reduce((total, shipment) => total + shipment?.count, 0);
            const totalExceptionsPercentage = ((totalExceptions / total_shipments) * 100).toFixed(2);

            let exceptionCount = 0;

            if (!shipment_statuses || shipment_statuses.length === 0) {
                // If shipment_statuses is null or empty, set all the states to 0
                setException({ count: 0, percentage: 0 });
                setUnclaimed({ count: 0, percentage: 0 });
                setRetainedCustom({ count: 0, percentage: 0 });
                setShipmentDamage({ count: 0, percentage: 0 });
                setShipmentCancel({ count: 0, percentage: 0 });
                setRefusedCustomer({ count: 0, percentage: 0 });
                setReturnSender({ count: 0, percentage: 0 });
                setReturningSender({ count: 0, percentage: 0 });
            }
            else {
                setException({ count: 0, percentage: 0 });
                setUnclaimed({ count: 0, percentage: 0 });
                setRetainedCustom({ count: 0, percentage: 0 });
                setShipmentDamage({ count: 0, percentage: 0 });
                setShipmentCancel({ count: 0, percentage: 0 });
                setRefusedCustomer({ count: 0, percentage: 0 });
                setReturnSender({ count: 0, percentage: 0 });
                setReturningSender({ count: 0, percentage: 0 });
                shipment_statuses?.forEach((item) => {
                    const percentage = totalExceptions > 0 ? ((item.count / totalExceptions) * 100).toFixed(2) : 0;
                    switch (item.shipment_substatus) {
                        case "abnormal_01":
                            setUnclaimed({count: item.count, percentage});
                            break;
                        case "abnormal_02":
                            setRetainedCustom({count: item.count, percentage});
                            break;
                        case "abnormal_03":
                            setShipmentDamage({count: item.count, percentage});
                            break;
                        case "abnormal_04":
                            setShipmentCancel({count: item.count, percentage});
                            break;
                        case "abnormal_05":
                            setRefusedCustomer({count: item.count, percentage});
                            break;
                        case "abnormal_06":
                            setReturnSender({count: item.count, percentage});
                            break;
                        case "abnormal_07":
                            setReturningSender({count: item.count, percentage});
                            break;
                        default:
                            exceptionCount += item.count;
                            break;
                    }
                    const exceptionPercentage = totalExceptions > 0 ? ((exceptionCount / totalExceptions) * 100).toFixed(2) : 0;
                    setException({count: exceptionCount, exceptionPercentage});
                });

            }

            setCarriersOptions(
                carriers?.map((item) => ({
                    label: item.tracking_company,
                    value: item.tracking_company,
                })),
            );
            setDestinationsOptions(
                destinations?.map((item) => ({
                    label: item.country,
                    value: item.country,
                })),
            );
            setShipmentStatus(shipment_statuses);
            setShipments(shipments);
            setShipmentsByCarrier(shipments_by_carrier);
            setTotalExceptions(totalExceptions);
            setExceptionsPercentage(totalExceptionsPercentage);
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

    const togglePopoverActiveOrderDate = useCallback(() => setPopoverActiveOrderDate((popoverActiveOrderDate) => !popoverActiveOrderDate), []);
    const togglePopoverActiveCarrier = useCallback(() => setPopoverActiveCarrier((popoverActiveCarrier) => !popoverActiveCarrier), []);
    const togglePopoverActiveDestination = useCallback(() => setPopoverActiveDestination((popoverActiveDestination) => !popoverActiveDestination), []);
    const handleChangeCarrier = useCallback((value) => {
        setSelectedCarrier(value);
        setToggleData(true);
        setLoading(true);
    }, []);
    const handleChangeDestination = useCallback((value) => {
        setSelectedDestination(value);
        setToggleData(true);
        setLoading(true);
    }, []);

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

    const option = {
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
            data: shipments?.map((shipment) => shipment?.date),
        },
        yAxis: {
            type: "value",
        },
        series: [
            {
                data: shipments?.map((shipment) => shipment?.shipments),
                type: "line", // Change from "line" to "bar"
            },
        ],
    };
    const groupedByCarrier = shipmentsByCarrier?.reduce((acc, shipment) => {
        if (!acc[shipment.tracking_company]) {
            acc[shipment.tracking_company] = [];
        }
        acc[shipment.tracking_company].push({
            date: shipment.date,
            shipments: shipment.shipments,
        });
        return acc;
    }, {});

// Extract unique dates for xAxis
    const dates = [...new Set(shipmentsByCarrier?.map((shipment) => shipment.date))];

// Generate series data for each carrier
    const series = Object.keys(groupedByCarrier).map((carrier) => ({
        name: carrier,
        type: "line", // Use "bar" for bar charts
        data: dates.map(
            (date) =>
                groupedByCarrier[carrier].find((entry) => entry.date === date)?.shipments || 0
        ), // Fill missing dates with 0 shipments
    }));

// ECharts configuration
    const optionByCarrier = {
        title: {
            text: "Shipments by Carrier",
            left: "center",
        },
        legend: {
            data: Object.keys(groupedByCarrier), // List of tracking_company names
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
            data: dates, // Dates on the x-axis
        },
        yAxis: {
            type: "value",
        },
        series, // Dynamically generated series
    };
    useEffect(() => {
        const handleResize = () => {
            const isMobile = window.innerWidth < 768;

            setChartSize({
                width: '100%',
                height: isMobile ? '300px' : '400px',
                legendOrient: isMobile ? 'horizontal' : 'vertical', // Change orientation on mobile
                legendPosition: isMobile ? { left: 'center', top: 'bottom' } : { left: 'right', top: 'center' }, // Position below pie chart on mobile
                chartRadius: isMobile ? ['30%', '50%'] : ['40%', '70%'], // Adjust the radius for mobile to create space

            });
        };

        // Initial resize check
        handleResize();

        // Listen to window resize events
        window.addEventListener('resize', handleResize);

        // Cleanup event listener on component unmount
        return () => {
            window.removeEventListener('resize', handleResize);
        };
    }, []);
    const optionPieData = [
        { name: 'Exception',value: exception?.count },
        { name: 'Unclaimed',value: unclaimed?.count },
        { name: 'Retained by custom',value: retainedCustom?.count },
        { name: 'Shipment damaged',value: shipmentDamage?.count },
        { name: 'Shipment cancelled',value: shipmentCancel?.count },
        { name: 'Refused by customer',value: refusedCustomer?.count },
        { name: 'Returned to sender',value: returnSender?.count },
        { name: 'Returning to customer',value: returningSender?.count }
    ];

    const optionPieDataTotal = optionPieData.reduce((sum, item) => sum + item.value, 0);
    const optionPie = {
        tooltip: {
            trigger: 'item'
        },
        legend: {
            orient: chartSize.legendOrient, // Dynamic legend orientation
            left: chartSize.legendPosition.left, // Dynamic legend position
            top: chartSize.legendPosition.top,  // Dynamic legend top positio
            align: 'left', // Align the text to the left
            icon: 'circle',
            textStyle: {
                fontSize: 12,
                fontWeight: 'normal',
            },
            formatter: (name) => {
                const item = optionPieData.find((item) => item.name === name);
                const percentage = item.value ? ((item.value / optionPieDataTotal) * 100).toFixed(2) : 0;
                return `${name} (${percentage}%)`;
            },
            // selectedMode: false,
        },
        series: [
            {
                name: '',
                type: 'pie',
                radius: chartSize.chartRadius, // Dynamic radius based on screen size
                center: ['35%', '50%'],
                avoidLabelOverlap: false,
                label: {
                    show: true,
                    fontSize: 16,
                    fontWeight: 'bold',
                    formatter: `Total: ${optionPieDataTotal}`, // Display the total number of records
                    position: 'center'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 16,
                        fontWeight: 'bold',
                        formatter: `Total: ${optionPieDataTotal}`, // Display the total number of records
                        // position: 'center',
                    }
                },
                labelLine: {
                    show: false
                },
                color: ['#6d7175','rgb(4,215,231)','rgb(0, 160, 172)', 'rgb(30, 147, 235)', 'rgb(252, 175, 48)', 'rgb(27, 190, 115)', 'rgb(253, 87, 73)', '#babec3'],
                data: optionPieData
            }
        ]
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
                        <Popover
                            active={popoverActiveCarrier}
                            activator={
                                <Button onClick={togglePopoverActiveCarrier} size="medium" textAlign="center" disclosure>
                                    <Text as="span" variant="bodySm" fontWeight="medium">
                                        Carrier
                                    </Text>
                                </Button>
                            }
                            autofocusTarget="first-node"
                            onClose={togglePopoverActiveCarrier}
                        >
                            <Popover.Section>
                                <ChoiceList allowMultiple choices={carriersOptions} selected={selectedCarrier} onChange={handleChangeCarrier} />
                                <div className="mt-2">
                                    <Button
                                        size="medium"
                                        textAlign="center"
                                        variant="plain"
                                        onClick={() => {
                                            setSelectedCarrier([]);
                                            setLoading(true);
                                            setToggleData(true);
                                        }}
                                    >
                                        Clear
                                    </Button>
                                </div>
                            </Popover.Section>
                        </Popover>
                        <Popover
                            active={popoverActiveDestination}
                            activator={
                                <Button onClick={togglePopoverActiveDestination} size="medium" textAlign="center" disclosure>
                                    <Text as="span" variant="bodySm" fontWeight="medium">
                                        Destination
                                    </Text>
                                </Button>
                            }
                            autofocusTarget="first-node"
                            onClose={togglePopoverActiveDestination}
                        >
                            <Popover.Section>
                                <ChoiceList allowMultiple choices={destinationsOptions} selected={selectedDestination} onChange={handleChangeDestination} />
                                <div className="mt-2">
                                    <Button
                                        size="medium"
                                        textAlign="center"
                                        variant="plain"
                                        onClick={() => {
                                            setSelectedDestination([]);
                                            setLoading(true);
                                            setToggleData(true);
                                        }}
                                    >
                                        Clear
                                    </Button>
                                </div>
                            </Popover.Section>
                        </Popover>
                    </ButtonGroup>
                    <Grid gap={{ xs: "1rem", sm: "1rem", md: "1rem", lg: "1rem", xl: "1rem" }}>
                        <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 3, lg: 3, xl: 3 }}>
                            <Card>
                                <LegacyStack alignment="center">
                                    <LegacyStack.Item fill>
                                        {loading ? (
                                            <SkeletonDisplayText />
                                        ) : (
                                            <Text fontWeight="semibold" variant="headingMd" as="span">
                                                Exception rate
                                            </Text>
                                        )}
                                        <div className="mt-3">
                                            {loading ? (
                                                <SkeletonBodyText lines={1} />
                                            ) : (
                                                <InlineStack gap={"100"}>
                                                    <Text as="span" variant="headingLg">
                                                        {exceptionsPercentage > 0 ? exceptionsPercentage : "0"}%
                                                    </Text>
                                                </InlineStack>
                                            )}
                                        </div>
                                    </LegacyStack.Item>
                                </LegacyStack>
                            </Card>
                        </Grid.Cell>
                        <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 3, lg: 3, xl: 3 }}>
                            <Card>
                                <LegacyStack alignment="center">
                                    <LegacyStack.Item fill>
                                        {loading ? (
                                            <SkeletonDisplayText />
                                        ) : (
                                            <Text fontWeight="semibold" variant="headingMd" as="span">
                                                Total exceptions
                                            </Text>
                                        )}
                                        <div className="mt-3">
                                            {loading ? (
                                                <SkeletonBodyText lines={1} />
                                            ) : (
                                                <InlineStack gap={"100"}>
                                                    <Text as="span" variant="headingLg">
                                                        {totalExceptions > 0 ? totalExceptions : "0"}
                                                    </Text>
                                                </InlineStack>
                                            )}
                                        </div>
                                    </LegacyStack.Item>
                                </LegacyStack>
                            </Card>
                        </Grid.Cell>
                        <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 3, lg: 3, xl: 3 }}>
                            <Card>
                                <LegacyStack alignment="center">
                                    <LegacyStack.Item fill>
                                        {loading ? (
                                            <SkeletonDisplayText />
                                        ) : (
                                            <Text fontWeight="semibold" variant="headingMd" as="span">
                                                Returned
                                            </Text>
                                        )}
                                        <div className="mt-3">
                                            {loading ? (
                                                <SkeletonBodyText lines={1} />
                                            ) : (
                                                <InlineStack gap={"100"}>
                                                    <Text as="span" variant="headingLg">
                                                        {(returningSender.count + returnSender.count + refusedCustomer.count) > 0 ? (returningSender.count + returnSender.count + refusedCustomer.count) : "0"}
                                                    </Text>
                                                </InlineStack>
                                            )}
                                        </div>
                                    </LegacyStack.Item>
                                </LegacyStack>
                            </Card>
                        </Grid.Cell>
                        <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 3, lg: 3, xl: 3 }}>
                            <Card>
                                <LegacyStack alignment="center">
                                    <LegacyStack.Item fill>
                                        {loading ? (
                                            <SkeletonDisplayText />
                                        ) : (
                                            <Text fontWeight="semibold" variant="headingMd" as="span">
                                                Damaged & lost
                                            </Text>
                                        )}
                                        <div className="mt-3">
                                            {loading ? (
                                                <SkeletonBodyText lines={1} />
                                            ) : (
                                                <InlineStack gap={"100"}>
                                                    <Text as="span" variant="headingLg">
                                                        {shipmentDamage.count > 0 ? shipmentDamage.count : "0"}
                                                    </Text>
                                                </InlineStack>
                                            )}
                                        </div>
                                    </LegacyStack.Item>
                                </LegacyStack>
                            </Card>
                        </Grid.Cell>
                    </Grid>
                    <Card>
                        <BlockStack gap={"400"}>
                            {loading ? (
                                <SkeletonDisplayText />
                            ) : (
                                <Text as="h2" variant="headingSm">
                                    Exception shipments
                                </Text>
                            )}
                            {loading ? (
                                <SkeletonBodyText lines={15} />
                            ) : (
                                <ReactECharts className="._CardSection__ReactECharts_15o35_3" option={option} style={{ height: "400px", width: "100%" }} />
                            )}
                        </BlockStack>
                    </Card>
                    <InlineGrid columns={2} gap={"100"} >
                        <Card>
                            <BlockStack gap={"400"}>
                                {loading ? (
                                    <SkeletonDisplayText />
                                ) : (
                                    <Text as="h2" variant="headingSm">
                                        Top 5 exception shipments
                                    </Text>
                                )}
                                {loading ? (
                                    <SkeletonBodyText lines={15} />
                                ) : (
                                    <ReactECharts className="._CardSection__ReactECharts_15o35_3" option={optionByCarrier} style={{ height: "400px", width: "100%" }} />
                                )}
                            </BlockStack>
                        </Card>
                        <Card>
                            <BlockStack gap={"400"}>
                                {loading ? (
                                    <SkeletonDisplayText />
                                ) : (
                                    <Text as="h2" variant="headingSm">
                                        Exceptions by current status
                                    </Text>
                                )}
                                {loading ? (
                                    <SkeletonBodyText lines={15} />
                                ) : (
                                    <ReactECharts className="._CardSection__ReactECharts_15o35_3"
                                                  option={optionPie}
                                                  style={{ width: chartSize.width, height: chartSize.height }}

                                    />                                )}
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
