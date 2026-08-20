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

export default function AnalyticsOld() {
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
  const [exceptions, setExceptions] = useState(0);
  const [exceptionsPercentage, setExceptionsPercentage] = useState(0);
  const [shipments, setShipments] = useState([]);
  const [shipmentStatus, setShipmentStatus] = useState([]);

  const [delivered, setDelivered] = useState({ count: 0, percentage: 0 });
  const [pending, setPending] = useState({ count: 0, percentage: 0 });
  const [transit, setTransit] = useState({ count: 0, percentage: 0 });
  const [pickup, setPickup] = useState({ count: 0, percentage: 0 });
  const [inforeceived, setInforeceived] = useState({ count: 0, percentage: 0 });

  const [undelivered, setUndelivered] = useState({ count: 0, percentage: 0 });
  const [exception, setException] = useState({ count: 0, percentage: 0 });
  const [expired, setExpired] = useState({ count: 0, percentage: 0 });

  const [popoverActive, setPopoverActive] = useState(false);
  const [selected, setSelected] = useState(["Shipments"]);

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(
        `${apiUrl}analytics?shipment_date_datefilter=${activeDateRange?.title}&shipment_date_starting=${activeDateRange?.period?.since}&shipment_date_ending=${activeDateRange?.period?.until}&carrier=${selectedCarrier}&destinations=${selectedDestination}`,
        {
          headers: {
            Authorization: `Bearer ${sessionToken}`,
          },
        },
      );
      const { shipments, carriers, destinations, shipment_statuses } = response?.data;
      const totalShipments = shipment_statuses.reduce((total, shipment) => total + shipment?.count, 0);
      const totalDeliveryPerformance = shipment_statuses?.find((shipment) => shipment.shipment_status === "delivered")?.count || 0;
      const totalDeliveredPercentage = ((totalDeliveryPerformance / totalShipments) * 100).toFixed(2);
      const totalValidTracking = shipment_statuses
        ?.filter((shipment) => shipment.shipment_status !== "delivered" && shipment.shipment_status !== "exception")
        .reduce((total, shipment) => total + shipment?.count, 0);
      const totalValidTrackingPercentage = ((totalValidTracking / totalShipments) * 100).toFixed(2);
      const totalExceptions = shipment_statuses?.find((shipment) => shipment?.shipment_status === "exception")?.count || 0;
      const totalExceptionsPercentage = ((totalExceptions / totalShipments) * 100).toFixed(2);
      console.log("totalDeliveryPerformance", totalDeliveryPerformance);

      let pendingCount = 0;

      if (!shipment_statuses || shipment_statuses.length === 0) {
        // If shipment_statuses is null or empty, set all the states to 0
        setPending({ count: 0, percentage: 0 });
        setInforeceived({ count: 0, percentage: 0 });
        setDelivered({ count: 0, percentage: 0 });
        setTransit({ count: 0, percentage: 0 });
        setPickup({ count: 0, percentage: 0 });
        setUndelivered({ count: 0, percentage: 0 });
        setException({ count: 0, percentage: 0 });
        setExpired({ count: 0, percentage: 0 });
      } else {
        setPending({ count: 0, percentage: 0 });
        setInforeceived({ count: 0, percentage: 0 });
        setDelivered({ count: 0, percentage: 0 });
        setTransit({ count: 0, percentage: 0 });
        setPickup({ count: 0, percentage: 0 });
        setUndelivered({ count: 0, percentage: 0 });
        setException({ count: 0, percentage: 0 });
        setExpired({ count: 0, percentage: 0 });
        shipment_statuses?.forEach((item) => {
          const percentage = totalShipments > 0 ? ((item.count / totalShipments) * 100).toFixed(2) : 0;
          switch (item.shipment_status) {
            case "delivered":
              setDelivered({count: item.count, percentage});
              break;
            case "notfound":
            case "pending":
              pendingCount += item.count;
              break;
            case "transit":
              setTransit({count: item.count, percentage});
              break;
            case "info received":
              setInforeceived({count: item.count, percentage});
              break;
            case "pickup":
              setPickup({count: item.count, percentage});
              break;
            case "out for delivery":
              setUndelivered({count: item.count, percentage});
              break;
            case "exception":
              setException({count: item.count, percentage});
              break;
            case "expired":
              setExpired({count: item.count, percentage});
              break;
            default:
              break;
          }
        });
        const pendingPercentage = totalShipments > 0 ? ((pendingCount / totalShipments) * 100).toFixed(2) : 0;
        setPending({ count: pendingCount, percentage: pendingPercentage });
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
      setTotalShipments(totalShipments);
      setDeliveryPerformance(totalDeliveryPerformance);
      setDeliveryPerformancePercentage(totalDeliveredPercentage);
      setValidTracking(totalValidTracking);
      setValidTrackingPercentage(totalValidTrackingPercentage);
      setExceptions(totalExceptions);
      setExceptionsPercentage(totalExceptionsPercentage);
      console.log("totalShipments", totalShipments);
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
        type: "bar", // Change from "line" to "bar"
      },
    ],
  };


  const togglePopoverActive = useCallback(() => setPopoverActive((popoverActive) => !popoverActive), []);
  return (
    <Page
      title="Analytics"
      secondaryActions={
        <Popover
          active={popoverActive}
          activator={
            <Button size="medium" textAlign="center" onClick={togglePopoverActive} disclosure>
              {selected}
            </Button>
          }
          autofocusTarget="first-node"
          onClose={togglePopoverActive}
        >
          <OptionList
            onChange={setSelected}
            options={[
              { value: "Shipments", label: "Shipments" },
              { value: "Transit time", label: "Transit time" },
              { value: "Tracking page", label: "Tracking page" },
              { value: "Shipping notifications", label: "Shipping notifications" },
            ]}
            selected={selected}
          />
        </Popover>
      }
    >
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
                    <Button size="slim" icon={CalendarIcon} onClick={() => setPopoverActiveOrderDate(!popoverActiveOrderDate)}>
                      Order date: {buttonValue}
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
              <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 3, lg: 4, xl: 4 }}>
                <Card>
                  <LegacyStack alignment="center">
                    <LegacyStack.Item fill>
                      {loading ? (
                        <SkeletonDisplayText />
                      ) : (
                        <Tooltip hasUnderline content="The number and percentage of delivered shipments">
                          <Text fontWeight="semibold" variant="headingMd" as="span">
                            Delivery performance
                          </Text>
                        </Tooltip>
                      )}
                      <div className="mt-3">
                        {loading ? (
                          <SkeletonBodyText lines={1} />
                        ) : (
                          <InlineStack gap={"100"}>
                            <Text as="span" variant="headingLg">
                              {deliveryPerformance > 0 ? deliveryPerformance : "-"}
                            </Text>
                            {deliveryPerformancePercentage > 0 && (
                              <Text variant="bodyMd" fontWeight="semibold" tone="subdued">
                                ({deliveryPerformancePercentage}%)
                              </Text>
                            )}
                          </InlineStack>
                        )}
                      </div>
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                      <Icon tone="subdued" source={ChevronRightIcon} />
                    </LegacyStack.Item>
                  </LegacyStack>
                </Card>
              </Grid.Cell>
              <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 3, lg: 4, xl: 4 }}>
                <Card>
                  <LegacyStack alignment="center">
                    <LegacyStack.Item fill>
                      {loading ? (
                        <SkeletonDisplayText />
                      ) : (
                        <Tooltip hasUnderline content="The number and percentage of shipments correctly tracked">
                          <Text fontWeight="semibold" variant="headingMd" as="span">
                            Valid tracking
                          </Text>
                        </Tooltip>
                      )}
                      <div className="mt-3">
                        {loading ? (
                          <SkeletonBodyText lines={1} />
                        ) : (
                          <InlineStack gap={"100"}>
                            <Text as="span" variant="headingLg">
                              {validTracking > 0 ? validTracking : "-"}
                            </Text>
                            {validTrackingPercentage > 0 && (
                              <Text variant="bodyMd" fontWeight="semibold" tone="subdued">
                                ({validTrackingPercentage}%)
                              </Text>
                            )}
                          </InlineStack>
                        )}
                      </div>
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                      <Icon tone="subdued" source={ChevronRightIcon} />
                    </LegacyStack.Item>
                  </LegacyStack>
                </Card>
              </Grid.Cell>
              <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 3, lg: 4, xl: 4 }}>
                <Card>
                  <LegacyStack alignment="center">
                    <LegacyStack.Item fill>
                      {loading ? (
                        <SkeletonDisplayText />
                      ) : (
                        <Tooltip hasUnderline content="The number and percentage of exception shipments">
                          <Text fontWeight="semibold" variant="headingMd" as="span">
                            Exceptions
                          </Text>
                        </Tooltip>
                      )}
                      <div className="mt-3">
                        {loading ? (
                          <SkeletonBodyText lines={1} />
                        ) : (
                          <InlineStack gap={"100"}>
                            <Text as="span" variant="headingLg">
                              {exceptions > 0 ? exceptions : "-"}
                            </Text>
                            {exceptionsPercentage > 0 && (
                              <Text variant="bodyMd" fontWeight="semibold" tone="subdued">
                                ({exceptionsPercentage}%)
                              </Text>
                            )}
                          </InlineStack>
                        )}
                      </div>
                    </LegacyStack.Item>
                    <LegacyStack.Item>
                      <Icon tone="subdued" source={ChevronRightIcon} />
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
                    Shipments
                  </Text>
                )}
                {loading ? (
                  <SkeletonBodyText lines={2} />
                ) : (
                  <>
                    <Text as="h3" variant="headingLg">
                      {totalShipments}
                    </Text>
                    <Text as="span" variant="bodySm" fontWeight="semibold" tone="subdued">
                      SHIPMENTS OVER TIME
                    </Text>{" "}
                  </>
                )}
                {loading ? (
                  <SkeletonBodyText lines={15} />
                ) : (
                  <ReactECharts className="._CardSection__ReactECharts_15o35_3" option={option} style={{ height: "400px", width: "100%" }} />
                )}
              </BlockStack>
            </Card>
            <Grid gap={{ xs: "1rem", sm: "1rem", md: "1rem", lg: "1rem", xl: "1rem" }}>
              <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 6, lg: 6, xl: 6 }}>
                <Card>
                  <BlockStack gap={"400"}>
                    {loading ? (
                      <SkeletonDisplayText />
                    ) : (
                      <Text as="h2" variant="headingSm">
                        Shipment status
                      </Text>
                    )}
                    {loading ? (
                      <SkeletonBodyText lines={14} />
                    ) : (
                      <BlockStack gap={"300"}>
                        <div className="flex">
                          <div className="text-left" style={{ flex: "1 1 0%" }}>
                            <Text as="span" variant="bodyMd" fontWeight="medium">
                              Status
                            </Text>
                          </div>
                          <div className="text-right" style={{ flex: "1 1 0%" }}>
                            <Text as="span" variant="bodyMd" fontWeight="medium">
                              Quantity
                            </Text>
                          </div>
                          <div className="text-right" style={{ flex: "1 1 0%" }}>
                            <Text as="span" variant="bodyMd" fontWeight="medium">
                              Percentage
                            </Text>
                          </div>
                        </div>
                        <div className="flex">
                          <div className="items-center flex text-left" style={{ flex: "1 1 0%" }}>
                            <span
                              style={{
                                backgroundColor: "#6d7175",
                                borderRadius: "50%",
                                display: "inline-block",
                                marginRight: "8px",
                                width: "14px",
                                height: "14px",
                              }}
                            ></span>
                            <Text>Pending</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{pending?.count > 0 ? pending?.count : "-"}</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{pending?.percentage > 0 ? `${pending?.percentage}%` : "-"}</Text>
                          </div>
                        </div>
                         <div className="flex">
                        <div className="items-center flex text-left" style={{ flex: "1 1 0%" }}>
                          <span
                            style={{
                              backgroundColor: "rgb(4,215,231)",
                              borderRadius: "50%",
                              display: "inline-block",
                              marginRight: "8px",
                              width: "14px",
                              height: "14px",
                            }}
                          ></span>
                          <Text>Info received</Text>
                        </div>
                           <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                             <Text>{inforeceived?.count > 0 ? inforeceived?.count : "-"}</Text>
                           </div>
                           <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                             <Text>{inforeceived?.percentage > 0 ? `${inforeceived?.percentage}%` : "-"}</Text>
                           </div>
                      </div>
                        <div className="flex">
                        <div className="items-center flex text-left" style={{ flex: "1 1 0%" }}>
                          <span
                            style={{
                              backgroundColor: "rgb(0, 160, 172)",
                              borderRadius: "50%",
                              display: "inline-block",
                              marginRight: "8px",
                              width: "14px",
                              height: "14px",
                            }}
                          ></span>
                          <Text>Pickup</Text>
                        </div>
                        <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                          <Text>{pickup?.count > 0 ? pickup?.count : "-"}</Text>
                        </div>
                        <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                          <Text>{pickup?.percentage > 0 ? `${pickup?.percentage}%` : "-"}</Text>
                        </div>
                      </div>
                        <div className="flex">
                          <div className="items-center flex text-left" style={{ flex: "1 1 0%" }}>
                            <span
                              style={{
                                backgroundColor: "rgb(30, 147, 235)",
                                borderRadius: "50%",
                                display: "inline-block",
                                marginRight: "8px",
                                width: "14px",
                                height: "14px",
                              }}
                            ></span>
                            <Text>In transit</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{transit > 0 ? transit : "-"}</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{transit?.percentage > 0 ? `${transit?.percentage}%` : "-"}</Text>
                          </div>
                        </div>
                        <div className="flex">
                          <div className="items-center flex text-left" style={{ flex: "1 1 0%" }}>
                            <span
                              style={{
                                backgroundColor: "rgb(252, 175, 48)",
                                borderRadius: "50%",
                                display: "inline-block",
                                marginRight: "8px",
                                width: "14px",
                                height: "14px",
                              }}
                            ></span>
                            <Text>Out for delivery</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{undelivered?.count > 0 ? undelivered?.count : "-"}</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{undelivered?.percentage > 0 ? `${undelivered?.percentage}%` : "-"}</Text>
                          </div>
                        </div>
                        <div className="flex">
                          <div className="items-center flex text-left" style={{ flex: "1 1 0%" }}>
                            <span
                              style={{
                                backgroundColor: "rgb(27, 190, 115)",
                                borderRadius: "50%",
                                display: "inline-block",
                                marginRight: "8px",
                                width: "14px",
                                height: "14px",
                              }}
                            ></span>
                            <Text>Delivered</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{delivered?.count > 0 ? delivered?.count : "-"}</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{delivered?.percentage > 0 ? `${delivered?.percentage}%` : "-"}</Text>
                          </div>
                        </div>
                        <div className="flex">
                          <div className="items-center flex text-left" style={{ flex: "1 1 0%" }}>
                            <span
                              style={{
                                backgroundColor: "rgb(253, 87, 73)",
                                borderRadius: "50%",
                                display: "inline-block",
                                marginRight: "8px",
                                width: "14px",
                                height: "14px",
                              }}
                            ></span>
                            <Text>Exception</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{exception?.count > 0 ? exception?.count : "-"}</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{exception?.percentage > 0 ? `${exception?.percentage}%` : "-"}</Text>
                          </div>
                        </div>
                        {/* <div className="flex">
                        <div className="items-center flex text-left" style={{ flex: "1 1 0%" }}>
                          <span
                            style={{
                              backgroundColor: "rgb(129, 9, 255)",
                              borderRadius: "50%",
                              display: "inline-block",
                              marginRight: "8px",
                              width: "14px",
                              height: "14px",
                            }}
                          ></span>
                          <Text>Failed attempt</Text>
                        </div>
                        <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                          <Text>-</Text>
                        </div>
                        <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                          <Text>-</Text>
                        </div>
                      </div> */}
                        <div className="flex">
                          <div className="items-center flex text-left" style={{ flex: "1 1 0%" }}>
                            <span
                              style={{
                                backgroundColor: "#babec3",
                                borderRadius: "50%",
                                display: "inline-block",
                                marginRight: "8px",
                                width: "14px",
                                height: "14px",
                              }}
                            ></span>
                            <Text>Expired</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{expired?.count > 0 ? expired?.count : "-"}</Text>
                          </div>
                          <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                            <Text>{expired?.percentage > 0 ? `${expired?.percentage}%` : "-"}</Text>
                          </div>
                        </div>
                      </BlockStack>
                    )}
                  </BlockStack>
                </Card>
              </Grid.Cell>
            </Grid>
          </BlockStack>
        </Layout.Section>
        <Layout.Section></Layout.Section>
        <Layout.Section></Layout.Section>
      </Layout>
    </Page>
  );
}
