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
  Icon, IndexTable,
  InlineGrid,
  InlineStack,
  Layout,
  LegacyStack, Link,
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
import {ArrowRightIcon, CalendarIcon, ChevronRightIcon, ExternalSmallIcon} from "@shopify/polaris-icons";
import React, { useCallback, useContext, useEffect, useRef, useState } from "react";
import ReactECharts from "echarts-for-react";
import { AppContext } from "../../components";
import { useAppBridge } from "@shopify/app-bridge-react";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import TableSkeletonWithTabs from "../../components/TableSkeletonWithTabs.jsx";

export default function TransitTime() {
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
  const [chartSize, setChartSize] = useState({
    width: '100%',
    height: '400px',
    // legendOrient: 'vertical', // Default legend orientation
    legendOrient: 'horizontal', // Default legend orientation
    // legendPosition: { left: 'center', top: 'bottom' }, // Default legend position
    legendPosition: { left: 'right', top: 'center' }, // Default legend position
    chartRadius: ['40%', '70%'], // No extra padding on desktop
  });
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
  const [shipments, setShipments] = useState([]);
  const [shipmentByCarrier, setShipmentByCarrier] = useState([]);

  const [transitTimeAvg, setTransitTimeAvg] = useState(0);
  const [processingTimeAvg, setProcessingTimeAvg] = useState(0);

  const resourceName = {
    singular: "shipment",
    plural: "shipments",
  };
  const [processingTime_0_3, setProcessingTime_0_3] = useState({ count: 0, percentage: 0 });
  const [processingTime_4_7, setProcessingTime_4_7] = useState({ count: 0, percentage: 0 });
  const [processingTime_8_11, setProcessingTime_8_11] = useState({ count: 0, percentage: 0 });
  const [processingTime_12_15, setProcessingTime_12_15] = useState({ count: 0, percentage: 0 });
  const [processingTime_16_30, setProcessingTime_16_30] = useState({ count: 0, percentage: 0 });
  const [processingTime_30_plus, setProcessingTime_30_plus] = useState({ count: 0, percentage: 0 });
  const rowMarkup = shipmentByCarrier?.map(
      (
          {
            tracking_company,
            country,
            avg_transit_time,
            max_transit_time,
            min_transit_time,
            day_0_3,
            day_4_7,
            day_8_11,
            day_12_15,
            day_16_30,
            day_30_plus,
          },
          index,
      ) => {
        return (
            <IndexTable.Row id={index} key={index} position={index} >
              <IndexTable.Cell>{tracking_company}</IndexTable.Cell>
              <IndexTable.Cell>{country}</IndexTable.Cell>
              <IndexTable.Cell>{Math.round(avg_transit_time)}</IndexTable.Cell>
              <IndexTable.Cell>{max_transit_time}</IndexTable.Cell>
              <IndexTable.Cell>{min_transit_time}</IndexTable.Cell>
              <IndexTable.Cell>{day_0_3}</IndexTable.Cell>
              <IndexTable.Cell>{day_4_7}</IndexTable.Cell>
              <IndexTable.Cell>{day_8_11}</IndexTable.Cell>
              <IndexTable.Cell>{day_12_15}</IndexTable.Cell>
              <IndexTable.Cell>{day_16_30}</IndexTable.Cell>
              <IndexTable.Cell>{day_30_plus}</IndexTable.Cell>
            </IndexTable.Row>
        );
      },
  );


  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(
        `${apiUrl}analytics/transit_time?shipment_date_datefilter=${activeDateRange?.title}&shipment_date_starting=${activeDateRange?.period?.since}&shipment_date_ending=${activeDateRange?.period?.until}&carrier=${selectedCarrier}&destinations=${selectedDestination}`,
        {
          headers: {
            Authorization: `Bearer ${sessionToken}`,
          },
        },
      );
      const { shipments, carriers, destinations,shipments_by_carrier } = response?.data;

      let transitSum = 0, processingTimeSum = 0;
      let transitCount = 0, processingTimeCount = 0;
      if (!shipments || shipments.length === 0) {
        // If shipment_statuses is null or empty, set all the states to 0


        setProcessingTime_0_3({ count: 0, percentage: 0 });
        setProcessingTime_4_7({ count: 0, percentage: 0 });
        setProcessingTime_8_11({ count: 0, percentage: 0 });
        setProcessingTime_12_15({ count: 0, percentage: 0 });
        setProcessingTime_16_30({ count: 0, percentage: 0 });
        setProcessingTime_30_plus({ count: 0, percentage: 0 });

      } else {

        // Initialize totals and counts for order-to-delivery and processing time ranges
        let totalPT = 0;



        let processingTimeRanges = {
          PT03: { count: 0, percentage: 0 },
          PT47: { count: 0, percentage: 0 },
          PT811: { count: 0, percentage: 0 },
          PT1215: { count: 0, percentage: 0 },
          PT1630: { count: 0, percentage: 0 },
          PT30p: { count: 0, percentage: 0 }
        };

// Calculate counts for each range based on the data in `shipments`
        shipments?.forEach(item => {
          // Convert each count to a number before adding

          // Processing time counts
          totalPT += Number(item.processing_time_0_3) + Number(item.processing_time_4_7) + Number(item.processing_time_8_11) +
              Number(item.processing_time_12_15) + Number(item.processing_time_16_30) + Number(item.processing_time_30_plus);

          processingTimeRanges.PT03.count += Number(item.processing_time_0_3);
          processingTimeRanges.PT47.count += Number(item.processing_time_4_7);
          processingTimeRanges.PT811.count += Number(item.processing_time_8_11);
          processingTimeRanges.PT1215.count += Number(item.processing_time_12_15);
          processingTimeRanges.PT1630.count += Number(item.processing_time_16_30);
          processingTimeRanges.PT30p.count += Number(item.processing_time_30_plus);

          if (item.processing_time !== null) {
            processingTimeSum += Number(item.processing_time);
            processingTimeCount++;
          }
          if (item.avg_processing_time !== null) {
            transitSum += Number(item.avg_processing_time);
            transitCount++;
          }
        });


        Object.keys(processingTimeRanges).forEach(key => {
          processingTimeRanges[key].percentage = totalPT > 0 ? ((processingTimeRanges[key].count / totalPT) * 100).toFixed(2) : 0;
        });
        setProcessingTime_0_3(processingTimeRanges.PT03);
        setProcessingTime_4_7(processingTimeRanges.PT47);
        setProcessingTime_8_11(processingTimeRanges.PT811);
        setProcessingTime_12_15(processingTimeRanges.PT1215);
        setProcessingTime_16_30(processingTimeRanges.PT1630);
        setProcessingTime_30_plus(processingTimeRanges.PT30p);

      }
// Calculate average order-to-delivery and processing times
      const averageTransitTime = transitCount > 0 ? Math.round(transitSum / transitCount) : 0;

      const averageProcessingTime = processingTimeCount > 0 ? Math.round(processingTimeSum / processingTimeCount) : 0;

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
      setShipments(shipments);
      setShipmentByCarrier(shipments_by_carrier);
      setTransitTimeAvg(averageTransitTime);
      setProcessingTimeAvg(averageProcessingTime);
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

  const optionProcessTime = {
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
        data: shipments?.map((shipment) => shipment?.processing_time),
        type: "bar", // Change from "line" to "bar"
      },
    ],
  };
  const optionTransitTimeAvg = {
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
        data: shipments?.map((shipment) => shipment?.avg_processing_time),
        type: "bar", // Change from "line" to "bar"
      },
    ],
  };
  const optionPieDataProcessTime = [
    { name: '0-3d',value: processingTime_0_3?.count },
    { name: '4-7d',value: processingTime_4_7?.count },
    { name: '8-11d',value: processingTime_8_11?.count },
    { name: '12-15d',value: processingTime_12_15?.count },
    { name: '16-30d',value: processingTime_16_30?.count },
    { name: '30+d',value: processingTime_30_plus.count }
  ];
  const optionPieDataTotalProcessTime = optionPieDataProcessTime.reduce((sum, item) => sum + item.value, 0);
  const optionPieProcessTime = {
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
        const item = optionPieDataProcessTime.find((item) => item.name === name);
        const percentage = item.value ? ((item.value / optionPieDataTotalProcessTime) * 100).toFixed(2) : 0;
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
          formatter: `Total: ${optionPieDataTotalProcessTime}`, // Display the total number of records
          position: 'center'
        },
        emphasis: {
          label: {
            show: true,
            fontSize: 16,
            fontWeight: 'bold',
            formatter: `Total: ${optionPieDataTotalProcessTime}`, // Display the total number of records
            // position: 'center',
          }
        },
        labelLine: {
          show: false
        },
        color: ['#1E93EB','#04D7E7','#1BBE73', '#fde43b', '#FCAF30', '#FD5749'],
        data: optionPieDataProcessTime
      }
    ]
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
                    <Button size="slim" icon={CalendarIcon} onClick={() => setPopoverActiveOrderDate(!popoverActiveOrderDate)}  disclosure="select">
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
            <InlineGrid columns={2} gap={"100"} >
              <Card>
              <BlockStack gap={"400"}>
                {loading ? (
                  <SkeletonDisplayText />
                ) : (
                    <InlineStack gap={"100"}>
                      <Tooltip hasUnderline content="It shows the time that 85% of shipments took to reach their destination.">
                        <Text fontWeight="semibold" variant="headingMd" as="h2">
                          P85 transit time (days)
                        </Text>
                      </Tooltip>

                    </InlineStack>


                )}
                {loading ? (
                  <SkeletonBodyText lines={2} />
                ) : (
                  <>
                    <Text as="h3" variant="headingLg">
                      {processingTimeAvg}
                    </Text>

                  </>
                )}
                {loading ? (
                  <SkeletonBodyText lines={15} />
                ) : (
                  <ReactECharts className="._CardSection__ReactECharts_15o35_3" option={optionProcessTime}  style={{ width: chartSize.width, height: chartSize.height }} />
                )}
              </BlockStack>
            </Card>
              <Card>
                <BlockStack gap={"400"}>
                  {loading ? (
                      <SkeletonDisplayText />
                  ) : (
                    <InlineStack gap={"100"}>
                      <Tooltip hasUnderline content="It shows the transit time based on the number of days.">
                        <Text fontWeight="semibold" variant="headingMd" as="h2">
                          Transit time distribution
                        </Text>
                      </Tooltip>
                    </InlineStack>
                  )}
                  {loading ? (
                      <SkeletonBodyText lines={10} />
                  ) : (
                      <BlockStack gap={"300"}>
                        <ReactECharts className="._CardSection__ReactECharts_15o35_3"
                                      option={optionPieProcessTime}
                                      style={{ width: chartSize.width, height: chartSize.height }}

                        />

                      </BlockStack>
                  )}
                </BlockStack>
              </Card>
            </InlineGrid>
            <Card>
              <BlockStack gap={"400"}>
                {loading ? (
                    <SkeletonDisplayText />
                ) : (
                    <InlineStack gap={"100"}>
                      <Tooltip hasUnderline content="It shows the average time that shipments take to reach their destination.">
                        <Text fontWeight="semibold" variant="headingMd" as="h2">
                          Average transit time (days)
                        </Text>
                      </Tooltip>

                    </InlineStack>


                )}
                {loading ? (
                    <SkeletonBodyText lines={2} />
                ) : (
                    <>
                      <Text as="h3" variant="headingLg">
                        {transitTimeAvg}
                      </Text>

                    </>
                )}
                {loading ? (
                    <SkeletonBodyText lines={10} />
                ) : (
                    <ReactECharts className="._CardSection__ReactECharts_15o35_3" option={optionTransitTimeAvg}  style={{ width: chartSize.width, height: chartSize.height }} />
                )}
              </BlockStack>
            </Card>
            <Card>
              <BlockStack gap={"400"}>
                {loading ? (
                    <SkeletonDisplayText />
                ) : (
                    <InlineStack gap={"100"}>
                      <Tooltip hasUnderline content="It shows the average time that shipments take to reach their destination.">
                        <Text fontWeight="semibold" variant="headingMd" as="h2">
                          Transit time details
                        </Text>
                      </Tooltip>
                    </InlineStack>
                )}
                {loading ? (
                    <SkeletonBodyText lines={5} />
                ) : (<>
                  <div className="OrderTable">
                    <IndexTable
                        resourceName={resourceName}
                        itemCount={shipmentByCarrier?.length}
                        selectable={false}
                        headings={[
                          { title: "Carrier" },
                          { title: "Destination" },
                          // { title: "Tracking number Extra Column", hidden: true },
                          { title: "Avg" },
                          { title: "Max" },
                          { title: "Min" },
                          { title: "0~3 d" },
                          { title: "4~7 d" },
                          { title: "8~11 d" },
                          { title: "12~15 d" },
                          { title: "16~30 d" },
                          { title: "30+ d" },
                        ]}
                    >
                      {rowMarkup}
                    </IndexTable>
                  </div>

                </>)}
              </BlockStack>
            </Card>
          </BlockStack>
        </Layout.Section>
        <Layout.Section></Layout.Section>
        <Layout.Section></Layout.Section>
      </Layout>
  );
}
