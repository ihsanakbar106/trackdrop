import {
  ActionList,
  Badge, MediaCard, VideoThumbnail,
  BlockStack, ProgressBar,
  Box, Link, Modal,
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
  Tooltip, Banner,
  useBreakpoints, RadioButton,
} from "@shopify/polaris";
import appBannerImg from "../assets/appBanner.jpeg";
import appVedioHebrew from "../assets/AutoTrackHebrew.mp4";
import appVedioEnglish from "../assets/AutoTrackHebrew.mp4";

import { ArrowRightIcon, CalendarIcon, ChevronRightIcon } from "@shopify/polaris-icons";
import React, { useCallback, useContext, useEffect, useRef, useState } from "react";
import ReactECharts from "echarts-for-react";
import { AppContext } from "../components";
import { useAppBridge } from "@shopify/app-bridge-react";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import {useNavigate} from "react-router-dom";
import { StarIcon,StarFilledIcon,NoteIcon } from '@shopify/polaris-icons';
import ShipmentImage from "../assets/shipment_performance.png";
import ShopifyImage from "../assets/ShopifyImage.png";
import Modern from "../assets/Modern.png";
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {faStar} from '@fortawesome/free-solid-svg-icons';
export default function Dashboard() {
  const appBridge = useAppBridge();
  const { apiUrl } = useContext(AppContext);
  const navigate = useNavigate();
  const [chartSize, setChartSize] = useState({
    width: '100%',
    height: '400px',
    legendOrient: 'vertical', // Default legend orientation
    legendPosition: { left: 'right', top: 'center' }, // Default legend position
    chartRadius: ['40%', '70%'], // No extra padding on desktop
  });
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
  const [noUpdateShipment, setNoUpdateShipment] = useState(0);
  const [undeliveredShipment, setUndeliveredShipment] = useState(0);
  const [exceptionShipment, setExceptionShipment] = useState(0);
  const [avgRatings, setAvgRatings] = useState(0);
  const [ratings, setRatings] = useState([
    { label: '5 stars', value: 0, percent: 0 },
    { label: '4 stars', value: 0, percent: 0 },
    { label: '3 stars', value: 0, percent: 0 },
    { label: '2 stars', value: 0, percent: 0 },
    { label: '1 star', value: 0 , percent: 0},
  ]);

  const datePickerRef = useRef(null);
  const VALID_YYYY_MM_DD_DATE_REGEX = /^\d{4}-\d{1,2}-\d{1,2}/;
  const [popoverActiveOrderDate, setPopoverActiveOrderDate] = useState(false);
  const [popoverActivePlan, setPopoverActivePlan] = useState(false);
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
  const [currentPlan, setCurrentPlan] = useState({});
  const [planUsage, setPlanUsage] = useState(0);

  const [delivered, setDelivered] = useState({ count: 0, percentage: 0 });
  const [pending, setPending] = useState({ count: 0, percentage: 0 });
  const [inforeceived, setInforeceived] = useState({ count: 0, percentage: 0 });
  const [transit, setTransit] = useState({ count: 0, percentage: 0 });
  const [pickup, setPickup] = useState({ count: 0, percentage: 0 });
  const [undelivered, setUndelivered] = useState({ count: 0, percentage: 0 });
  const [exception, setException] = useState({ count: 0, percentage: 0 });
  const [expired, setExpired] = useState({ count: 0, percentage: 0 });

  const [popoverActive, setPopoverActive] = useState(false);
  const [vedioPopupActive, setVedioPopupActive] = useState(false);
  const [countryName, setCountryName] = useState('');
  const [selected, setSelected] = useState(["Shipments"]);

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(
          `${apiUrl}dashboard?shipment_date_datefilter=${activeDateRange?.title}&shipment_date_starting=${activeDateRange?.period?.since}&shipment_date_ending=${activeDateRange?.period?.until}&carrier=${selectedCarrier}&destinations=${selectedDestination}`,
          {
            headers: {
              Authorization: `Bearer ${sessionToken}`,
            },
          },
      );
      // console.log('Responce',response?.data);
      const { plan_id,country_name,active_plan,total_req,shipments, carriers, destinations, shipment_statuses,exception_shipment,undelivered_shipment,no_update_shipment,star_rating,avg_rating} = response?.data;
      if(!plan_id){
        navigate("/billing");
      }

      setCountryName(country_name);
      setPlanUsage(total_req);
      setCurrentPlan(active_plan);
      setRatings(star_rating);
      setAvgRatings(avg_rating);
      setExceptionShipment(exception_shipment);
      setUndeliveredShipment(undelivered_shipment);
      setNoUpdateShipment(no_update_shipment);
      const totalShipments = shipment_statuses.reduce((total, shipment) => total + shipment?.count, 0);
      const totalDeliveryPerformance = shipment_statuses?.find((shipment) => shipment.shipment_status === "delivered")?.count || 0;
      const totalDeliveredPercentage = ((totalDeliveryPerformance / totalShipments) * 100).toFixed(2);
      const totalValidTracking = shipment_statuses
          ?.filter((shipment) => shipment.shipment_status !== "delivered" && shipment.shipment_status !== "exception")
          .reduce((total, shipment) => total + shipment?.count, 0);
      const totalValidTrackingPercentage = ((totalValidTracking / totalShipments) * 100).toFixed(2);
      const totalExceptions = shipment_statuses?.find((shipment) => shipment?.shipment_status === "exception")?.count || 0;
      const totalExceptionsPercentage = ((totalExceptions / totalShipments) * 100).toFixed(2);
      // console.log("totalDeliveryPerformance", totalDeliveryPerformance);

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
      // console.log("totalShipments", totalShipments);
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
    // console.log("handleStartInputValueChange, validDate", value);
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
        color: "#666", // Change the text color of the legend
        fontSize: 12, // Change the font size of the legend text
      },
      itemWidth: 20,
      itemHeight: 10,
      itemGap: 5,
      // Set the icon shape to "rect" to display the legend as a box
      icon: "rect",
    },
    grid: {
      bottom: "15%", // Add some space at the bottom for the legend
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
        type: "line",
      },
    ],
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
    { name: 'Pending',value: pending?.count },
    { name: 'Info Received',value: inforeceived?.count },
    { name: 'Pickup',value: pickup?.count },
    { name: 'In transit',value: transit?.count },
    { name: 'Out for delivery',value: undelivered?.count },
    { name: 'Delivered',value: delivered?.count },
    { name: 'Exception',value: exception?.count },
    { name: 'Expired',value: expired?.count }
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

  const handleLegendSelect = (params) => {
    const selectedItem = params.name;
    // console.log('selectedItem',params)
    navigate("/orders?status="+selectedItem);
  };


  const handleVedioModalClose = useCallback(() => {
    setVedioPopupActive(false);
  }, []);
  const togglePopoverActive = useCallback(() => setPopoverActive((popoverActive) => !popoverActive), []);
  // Handle window resize to update chart size
    const handleSubmit = async () => {
        window.parent.location.href = 'mailto:help.mediascale@gmail.com';
    }

  useEffect(() =>{
    var stars = document.querySelectorAll('.star-rating .fa-star');

    for (var i = 0; i < stars.length; i++) {
      stars[i].addEventListener('click', function () {
        var ratingValue = parseInt(this.getAttribute('data-rating'));
        window.open('https://apps.shopify.com/autotrack-1?#modal-show=WriteReviewModal', '_blank');

      });

      stars[i].addEventListener('mouseover', function () {
        var ratingValue = parseInt(this.getAttribute('data-rating'));
        for (var j = 0; j < ratingValue; j++) {
          stars[j].classList.add('active');
        }
      });

      stars[i].addEventListener('mouseout', function () {
        for (var j = 0; j < stars.length; j++) {
          stars[j].classList.remove('active');
        }
      });
    }
  }, []);

  return (
      <Page
          title="Dashboard"
          primaryAction={{
              content: 'Contact us',
              onAction: handleSubmit,
          }}
      >
        <Layout>
          <Layout.Section variant="fullWidth">
            <BlockStack gap={"400"}>
              {loading ? (
                  <InlineGrid columns={2} alignItems={"center"} gap={100}>
                    <SkeletonDisplayText />
                    <ProgressBar size={"small"} tone={"primary"} progress={0} />

                  </InlineGrid>
              ) : (
                  <InlineGrid columns={['twoThirds', 'oneThird']} alignItems={"center"}  gap={100}>


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

                    </ButtonGroup>
                    <Popover
                        active={popoverActivePlan}
                        autofocusTarget="none"
                        preferredAlignment="left"
                        preferredPosition="below"
                        fluidContent
                        sectioned={true}
                        fullHeight
                        activator={
                      <div onClick={() => setPopoverActivePlan(!popoverActivePlan)}>
                        <div style={{display:"flex",alignItems:"center",justifyContent:"space-between",gap:"10px"}}>
                          <Text  as="p" variant="bodyMd">{currentPlan?.name}</Text>
                          <ProgressBar size={"small"} tone={"primary"} progress={currentPlan?.response_limit ? ((currentPlan?.response_limit-planUsage) / currentPlan.response_limit) * 100 : 0} />
                        </div>
                      </div>

                        }
                        onClose={() => setPopoverActivePlan(false)}
                        >
                      <Popover.Pane >
                        <Box
                            maxWidth={mdDown ? "516px" : "212px"}
                            width={mdDown ? "100%" : "212px"}
                            padding={{ xs: 500, md: 0 }}
                            paddingBlockEnd={{ xs: 100, md: 0 }}
                        >
                        <Card>
                        <Text variant="headingMd" as="h2"  fontWeight="semibold">
                          {currentPlan?.name}
                        </Text>
                        <Text variant="bodyMd" as="p">
                          Available/Total
                        </Text>
                        <Text variant="bodyMd" as="p">
                          <b>{currentPlan?.response_limit-planUsage}</b>/{currentPlan?.response_limit}
                        </Text>
                        <ProgressBar size={"small"} tone={"primary"} progress={currentPlan?.response_limit ? ((currentPlan?.response_limit-planUsage) / currentPlan.response_limit) * 100 : 0} />
                        </Card>
                        </Box>
                      </Popover.Pane>
                    </Popover>
                  </InlineGrid>

              )}
              <Banner title={
              <div className='dashboard-bar'>
                  <Text variant="headingSm" as="h6">
                    Would you mind letting us
                    know
                    what you think about this App?
                    <div className="star-rating">
                      <FontAwesomeIcon className="fa fa-star star1"
                                       data-rating="1" icon={faStar}/>
                      <FontAwesomeIcon className="fa fa-star star2"
                                       data-rating="2" icon={faStar}/>
                      <FontAwesomeIcon className="fa fa-star star3"
                                       data-rating="3" icon={faStar}/>
                      <FontAwesomeIcon className="fa fa-star star4"
                                       data-rating="4" icon={faStar}/>
                      <FontAwesomeIcon className="fa fa-star star5"
                                       data-rating="5" icon={faStar}/>
                    </div>
                  </Text>
                </div>
              }>
              </Banner>
              {loading ? (
                  <Card>
                  <SkeletonBodyText lines={6} />
                  </Card>
              ) : (
              <MediaCard
                  title="Getting Started with AutoTrack ‑ Order Tracking"
                  primaryAction={{
                    content: 'Learn more',
                    onAction: () => {setVedioPopupActive(true)},
                  }}
                  description={`Watch our tutorial video to see how our app tracks your shipments in real-time. Learn how to easily monitor the status of your orders and access detailed analytics. This step-by-step guide will show you how to use the app to stay on top of all your shipments and make informed decisions based on data. The video covers everything you need to know, from basic features to advanced functionalities.`}
                  size="small"
              >
                <VideoThumbnail
                    videoLength={300}
                    thumbnailUrl={appBannerImg}
                    onClick={() => setVedioPopupActive(true)}
                />
              </MediaCard>
                  )}
              <Card>
              <Grid gap={{ xs: "1rem", sm: "1rem", md: "1rem", lg: "1rem", xl: "1rem" }}>
                <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 3, lg: 4, xl: 4 }} >

                  <LegacyStack alignment="center">
                    <LegacyStack.Item fill>
                      <div className="dashboardTopTab" onClick={()=>{navigate('/orders?status=Exception')}}>
                      {loading ? (
                          <SkeletonDisplayText />
                      ) : (
                          // <Tooltip hasUnderline content="The number and percentage of exception shipments">
                            <Text fontWeight="regular" variant="headingMd" as="span">
                              Exceptions
                            </Text>
                          // </Tooltip>
                      )}
                      <div className="mt-3">
                        {loading ? (
                            <SkeletonBodyText lines={1} />
                        ) : (
                            <InlineStack gap={"100"}>
                              <Text as="span" variant="headingLg">
                                {exception.count}
                              </Text>
                            </InlineStack>
                        )}
                      </div>
                      </div>
                    </LegacyStack.Item>
                  </LegacyStack>

                </Grid.Cell>


                <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 3, lg: 4, xl: 4 }}>
                    <LegacyStack alignment="center">
                      <LegacyStack.Item fill>
                        <div className="dashboardTopTab" onClick={()=>{navigate('/orders?status=Expired')}}>

                        {loading ? (
                            <SkeletonDisplayText />
                        ) : (
                              <Text fontWeight="regular" variant="headingMd" as="span">
                                Expired
                              </Text>
                        )}
                        <div className="mt-3">
                          {loading ? (
                              <SkeletonBodyText lines={1} />
                          ) : (
                              <InlineStack gap={"100"}>
                                <Text as="span" variant="headingLg">
                                  {expired.count}
                                </Text>
                              </InlineStack>
                          )}
                        </div>
                        </div>
                      </LegacyStack.Item>
                    </LegacyStack>
                </Grid.Cell>
                <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 3, lg: 4, xl: 4 }}>
                    <LegacyStack alignment="center">
                      <LegacyStack.Item fill>
                        <div className="dashboardTopTab" onClick={()=>{navigate('/orders?status=Pending')}}>

                        {loading ? (
                            <SkeletonDisplayText />
                        ) : (
                              <Text fontWeight="regular" variant="headingMd" as="span">
                                Pending
                              </Text>
                        )}
                        <div className="mt-3">
                          {loading ? (
                              <SkeletonBodyText lines={1} />
                          ) : (
                              <InlineStack gap={"100"}>
                                <Text as="span" variant="headingLg">
                                  {pending.count}
                                </Text>
                              </InlineStack>
                          )}
                        </div>
                        </div>
                      </LegacyStack.Item>

                    </LegacyStack>
                </Grid.Cell>
              </Grid>
              </Card>
              <Card>
                <BlockStack gap={"400"}>
                  {loading ? (
                      <SkeletonDisplayText />
                  ) : (
                      <Text as="h2" variant="headingSm">
                        Shipment performance
                      </Text>
                  )}

                  {loading ? (
                      <SkeletonBodyText lines={6} />
                  ) : (
                      <InlineStack blockAlign={"center"} align={"space-between"}>
                    <BlockStack>
                      <InlineStack blockAlign={"center"} gap={100} align={"start"}>
                        <div
                            style={{ cursor: exceptionShipment > 0 ? "pointer" : "default" }}
                            onClick={exceptionShipment > 0 ? () => navigate('/orders?status=Exception') : undefined}
                        >
                        <Text as="h2" variant="headingSm">
                          {exceptionShipment} shipments
                        </Text>
                        </div>
                        <Text as="h2" variant="bodyMd">
                          in Exceptions status for more than 3 days
                        </Text>
                      </InlineStack>
                      <InlineStack blockAlign={"center"} gap={100} align={"start"}>
                        <div
                            style={{ cursor: undeliveredShipment > 0 ? "pointer" : "default" }}
                            onClick={undeliveredShipment > 0 ? () => navigate('/orders?status=Out for delivery') : undefined}
                        >
                        <Text as="h2" variant="headingSm">
                          {undeliveredShipment} shipments
                        </Text>
                        </div>
                        <Text as="h2" variant="bodyMd">
                           in Out for delivery status for more than 3 days
                        </Text>
                      </InlineStack>
                      <InlineStack blockAlign={"center"} gap={100} align={"start"}>
                        <div
                            style={{ cursor: noUpdateShipment > 0 ? "pointer" : "default" }}
                            onClick={noUpdateShipment > 0 ? () => navigate('/orders') : undefined}
                        >
                        <Text as="h2" variant="headingSm">
                          {noUpdateShipment} shipments
                        </Text>
                        </div>
                        <Text as="h2" variant="bodyMd">
                           have no updates for more than 2 weeks
                        </Text>
                      </InlineStack>
                    </BlockStack>

                        <img
                            // style={{ aspectRatio: "5/4", objectFit: "contain" }}
                            style={{ width: "100px" }}
                            src={ShipmentImage}
                        />
                    </InlineStack>
                  )}
                </BlockStack>
              </Card>
              <Grid gap={{ xs: "1rem", sm: "1rem", md: "1rem", lg: "1rem", xl: "1rem" }}>
                <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 12, lg: 12, xl: 12 }}>
                  <Card>
                    <BlockStack gap={"400"}>
                      {loading ? (
                          <SkeletonDisplayText />
                      ) : (
                          <Text as="h2" variant="headingSm">
                            Total shipments by status
                          </Text>
                      )}
                      {loading ? (
                          <SkeletonBodyText lines={10} />
                      ) : (
                          <BlockStack gap={"300"}>
                            <ReactECharts className="._CardSection__ReactECharts_15o35_3"
                                          option={optionPie}
                                          style={{ width: chartSize.width, height: chartSize.height }}
                                          onEvents={{
                                            'legendselectchanged': handleLegendSelect, // Listen for legend select events
                                          }}
                            />

                            {/*<BlockStack gap={"300"}>
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
                              backgroundColor: "rgb(0, 160, 172)",
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
                          <Text>-</Text>
                        </div>
                        <div className="items-center text-right" style={{ flex: "1 1 0%" }}>
                          <Text>-</Text>
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
                               <div className="flex">
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
                      </div>
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
                            </BlockStack>*/}

                          </BlockStack>
                      )}
                    </BlockStack>
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
              {/*<Card>
                <BlockStack gap={"400"}>
                  {loading ? (
                      <SkeletonDisplayText />
                  ) : (
                      <Text variant="headingSm">Average shipment review rating</Text>

                  )}
                  <BlockStack alignment="center">
                    {loading ? (
                        <SkeletonDisplayText />
                    ) : (
                        <>
                          <Text variant="heading2xl">{avgRatings}</Text>
                          <InlineStack>
                            <InlineStack spacing="extraTight">
                              {[...Array(5)].map((_, index) => (
                                  <Icon key={index}
                                        source={index < Math.floor(avgRatings) ? StarFilledIcon : StarIcon}
                                        tone={index < Math.floor(avgRatings) ? 'primary' : 'base'}
                                  />
                              ))}
                            </InlineStack>
                            {avgRatings>0?<></>:<Text>No reviews yet</Text>}

                          </InlineStack>
                        </>

                    )}

                  </BlockStack>
                  {loading ? (
                      <SkeletonBodyText lines={9} />
                  ) : (<>
                  {ratings.map((rating, index) => (
                      <InlineStack key={index} blockAlign={"center"} align={"space-between"}>
                        <Text>{rating.label}</Text>
                        <div className="progress-container">
                          <ProgressBar
                              progress={rating.percent}
                              tone="primary"
                              size="medium"
                              ariaLabelledBy={`progress-bar-${rating.value}`}
                              animated
                          />
                          <div className="progress-value">
                            {`${rating.value} (${rating.percent}%)`}
                          </div>
                        </div>

                      </InlineStack>
                  ))}</>)}
                </BlockStack>
              </Card>*/}


            </BlockStack>
            <Modal
                size="large"
                open={vedioPopupActive}
                onClose={handleVedioModalClose}
                title="Getting Started with AutoTrack ‑ Order Tracking"
            >
              <Modal.Section>
                <video style={{width:"100%",height:"350px"}}  controls controlslist="nodownload nofullscreen noremoteplayback">
                  <source src={countryName=="Israel"?appVedioHebrew:appVedioEnglish} type="video/mp4"/>

                </video>
              </Modal.Section>
            </Modal>
          </Layout.Section>
          <Layout.Section></Layout.Section>
          <Layout.Section></Layout.Section>
        </Layout>
      </Page>
  );
}
