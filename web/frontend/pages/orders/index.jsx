import { Toast, useAppBridge, useNavigate } from "@shopify/app-bridge-react";
import { useSearchParams } from 'react-router-dom';

import {
  Badge,
  Button,
  Card,
  ChoiceList,
  Icon,
  IndexFilters,
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
import { ExternalSmallIcon } from "@shopify/polaris-icons";
import React, { useCallback, useContext, useEffect, useState } from "react";
import { AppContext } from "../../components";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import { SkeltonPage, SkeltonPageForTable } from "../../components/SkeltonPage";
import TableSkeletonWithTabs from "../../components/TableSkeletonWithTabs";

function capitalizeWords(str) {
  return str.replace(/\b\w/g, (char) => char.toUpperCase());
}
function selectBadgeTone(shipment_status) {
  var tone="";
    switch (shipment_status) {
        case "delivered":
            tone="Delivered";
            break;
        case "notfound":
        case "pending":
            tone="Pending";
            break;
        case "transit":
            tone="Transit";
            break;
        case "info received":
            tone="InfoRecived";
            break;
        case "pickup":
            tone="Pickup";
            break;
        case "out for delivery":
            tone="OutForDelivery";
            break;
        case "exception":
            tone="Exception";
            break;
        case "expired":
            tone="Expired";
            break;
        default:
            break;
    }
  return tone;
}

export default function Orders() {
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
  const [itemStrings, setItemStrings] = useState(["All"]);
  const appBridge = useAppBridge();
  const { apiUrl, shop } = useContext(AppContext);
  const [toggleData, setToggleData] = useState(true);
  const [loading, setLoading] = useState(true);
  const [btnLoading, setBtnLoading] = useState(false);
  const [searchLoading, setSearchLoading] = useState(true);
  const [fulfillments, setFulfillments] = useState([]);
  const [carriersOptions, setCarriersOptions] = useState([]);
  const [originOptions, setOriginOptions] = useState([]);
  const [destinationsOptions, setDestinationsOptions] = useState([]);
  const [shipmentStatusOptions, setShipmentStatusOptions] = useState([]);
  const [orderCreateDate, setOrderCreateDate] = useState(undefined);
  const [orderCreateStartDate, setOrderCreateStartDate] = useState("");
  const [orderCreateEndDate, setOrderCreateEndDate] = useState("");
  const [shipmentCreateDate, setShipmentCreateDate] = useState(undefined);
  const [shipmentCreateStartDate, setShipmentCreateStartDate] = useState("");
  const [shipmentCreateEndDate, setShipmentCreateEndDate] = useState("");
  const [carrier, setCarrier] = useState(undefined);
  const [shipmentStatus, setShipmentStatus] = useState(undefined);
  const [fulfillmentStatus, setFulfillmentStatus] = useState(undefined);
  const [transitTime, setTransitTime] = useState(undefined);
  const [notes, setNotes] = useState(undefined);
  const [origin, setOrigin] = useState(undefined);
  const [destination, setDestination] = useState(undefined);
  const [queryValue, setQueryValue] = useState("");
  const [debounceTimeout, setDebounceTimeout] = useState(null);
  const [hasNextPage, setHasNextPage] = useState(true);
  const [hasPreviousPage, setHasPreviousPage] = useState(true);
  const [paginationValue, setPaginationValue] = useState(1);
  const [errorToast, setErrorToast] = useState(false);
  const [successToast, setSuccessToast] = useState(false);
  const [toastMsg, setToastMsg] = useState("");

  const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
  const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

  const toastErrorMsg = errorToast ? <Toast content={toastMsg} error onDismiss={toggleErrorMsgActive} /> : null;

  const toastSuccessMsg = successToast ? <Toast content={toastMsg} onDismiss={toggleSuccessMsgActive} /> : null;

  const handlePagination = (value) => {
    if (value == "next") {
      setPaginationValue(paginationValue + 1);
    } else {
      setPaginationValue(paginationValue - 1);
    }
    setToggleData(true);
  };

  const dateOptions = [
    { label: "Today", value: "Today" },
    { label: "Last 7 days", value: "Last 7 days" },
    { label: "Last 30 days", value: "Last 30 days" },
    { label: "Last 60 days", value: "Last 60 days" },
    { label: "Last 90 days", value: "Last 90 days" },
    { label: "Custom", value: "Custom" },
  ];

  const tabs = itemStrings.map((item, index) => ({
    content: item,
    index,
    onAction: () => {
      setSearchLoading(true);
      setToggleData(true);
    },
    id: `${item}-${index}`,
    isLocked: index === 0,
    actions: index === 0 ? [] : [],
  }));
  const [selected, setSelected] = useState(0);
  const onCreateNewView = async (value) => {
    await sleep(500);
    setItemStrings([...itemStrings, value]);
    setSelected(itemStrings.length);
    return true;
  };

  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(
        `${apiUrl}fulfillments?search=${queryValue}&order_date_datefilter=${orderCreateDate || ""}&order_date_starting=${
          orderCreateStartDate || ""
        }&order_date_ending=${orderCreateEndDate || ""}&shipment_date_datefilter=${
          shipmentCreateDate || ""
        }&shipment_date_starting=${shipmentCreateStartDate}&shipment_date_ending=${shipmentCreateEndDate}&carrier=${carrier || ""}&shipment_status=${
          shipmentStatus || ""
        }&destinations=${destination || ""}&origins=${origin || ""}&transit_time=${transitTime || ""}&notes=${
          notes || ""
        }&select_all_shipment_status=${tabs[selected].content?.charAt(0).toLowerCase() + tabs[selected].content.slice(1)}&page=${paginationValue}`,
        {
          headers: {
            Authorization: `Bearer ${sessionToken}`,
          },
        },
      );
      const { plan_id,fulfillments, carriers, origins, destinations, shipment_statuses, all_shipment_statuses } = response?.data;
      if(!plan_id){
        navigate("/billing");
      }
      setFulfillments(fulfillments?.data);
      setCarriersOptions(
        carriers?.map((item) => ({
          label: item.tracking_company,
          value: item.tracking_company,
        })),
      );
      setOriginOptions(
        origins?.map((item) => ({
          label: item.original_country,
          value: item.original_country,
        })),
      );
      setDestinationsOptions(
        destinations?.map((item) => ({
          label: item.country,
          value: item.country,
        })),
      );
      setShipmentStatusOptions(
        shipment_statuses?.map((item) => ({
          label: capitalizeWords(item.shipment_status) === "Notfound" ? "Pending" : capitalizeWords(item.shipment_status),
          value: item.shipment_status === "notfound" ? "pending" : item.shipment_status,
        })),
      );
      setItemStrings(all_shipment_statuses?.map((item) => item.charAt(0).toUpperCase() + item.slice(1)));
      setHasNextPage(fulfillments?.last_page > paginationValue);
      setHasPreviousPage(paginationValue > 1);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
      setToggleData(false);
      setSearchLoading(false);
      getParamsStatus();
    }
  };

  useEffect(() => {
    if (toggleData) {
      fetchData();
    }
  }, [
    toggleData,
    queryValue,
    orderCreateDate,
    orderCreateStartDate,
    orderCreateEndDate,
    shipmentCreateDate,
    shipmentCreateStartDate,
    shipmentCreateEndDate,
    shipmentStatus,
    carrier,
    destination,
    origin,
    transitTime,
    notes,
    selected,
  ]);

  const { mode, setMode } = useSetIndexFiltersMode();
  const onHandleCancel = () => {};

  const handleOrderCreateDateChange = useCallback((value) => {
    setOrderCreateDate(value);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleShipmentCreateDateChange = useCallback((value) => {
    setShipmentCreateDate(value);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleCarrierChange = useCallback((value) => {
    setCarrier(value);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleShipmentStatusChange = useCallback((value) => {
    setShipmentStatus(value);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleFulfillmentStatusChange = useCallback((value) => {
    setFulfillmentStatus(value);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleTransitTimeChange = useCallback((value) => {
    setTransitTime(value);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleNotesChange = useCallback((value) => {
    setNotes(value);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleOriginChange = useCallback((value) => {
    setOrigin(value);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleDestinationChange = useCallback((value) => {
    setDestination(value);
    setToggleData(true);
    setSearchLoading(true);
  }, []);

  const handleFiltersQueryChange = useCallback(
    (value) => {
      setQueryValue(value);

      // Clear the previous timeout if it exists
      if (debounceTimeout) {
        clearTimeout(debounceTimeout);
      }

      // Set a new timeout
      const timeoutId = setTimeout(() => {
        setToggleData(true);
        setSearchLoading(true);
      }, 400); // Adjust the delay as needed (e.g., 300ms)

      // Store the timeout ID
      setDebounceTimeout(timeoutId);
    },
    [debounceTimeout],
  );
  const handleOrderCreateDateRemove = useCallback(() => {
    setOrderCreateDate(undefined);
    setOrderCreateStartDate("");
    setOrderCreateEndDate("");
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleShipmentCreateDateRemove = useCallback(() => {
    setShipmentCreateDate(undefined);
    setShipmentCreateStartDate("");
    setShipmentCreateEndDate("");
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleCarrierRemove = useCallback(() => {
    setCarrier(undefined);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleShipmentStatusRemove = useCallback(() => {
    setShipmentStatus(undefined);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleFulfillmentStatusRemove = useCallback(() => {
    setFulfillmentStatus(undefined);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleTransitTimeRemove = useCallback(() => {
    setTransitTime(undefined);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleNotesRemove = useCallback(() => {
    setNotes(undefined);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleOriginRemove = useCallback(() => {
    setOrigin(undefined);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleDestinationRemove = useCallback(() => {
    setDestination(undefined);
    setToggleData(true);
    setSearchLoading(true);
  }, []);

  const handleQueryValueRemove = useCallback(() => setQueryValue(""), []);
  const handleFiltersClearAll = useCallback(() => {
    handleOrderCreateDateRemove();
    handleShipmentCreateDateRemove();
    handleCarrierRemove();
    handleShipmentStatusRemove();
    handleFulfillmentStatusRemove();
    handleQueryValueRemove();
    handleTransitTimeRemove();
    handleNotesRemove();
    handleOriginRemove();
    handleDestinationRemove();
  }, [
    handleOrderCreateDateRemove,
    handleShipmentCreateDateRemove,
    handleQueryValueRemove,
    handleCarrierRemove,
    handleShipmentStatusRemove,
    handleFulfillmentStatusRemove,
    handleTransitTimeRemove,
    handleNotesRemove,
    handleOriginRemove,
    handleDestinationRemove,
  ]);

  const handleChangeOrderCreateStartDate = useCallback((newValue) => {
    setOrderCreateStartDate(newValue);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleChangeOrderCreateEndDate = useCallback((newValue) => {
    setOrderCreateEndDate(newValue);
    setToggleData(true);
    setSearchLoading(true);
  }, []);

  const handleChangeShipmentCreateStartDate = useCallback((newValue) => {
    setShipmentCreateStartDate(newValue);
    setToggleData(true);
    setSearchLoading(true);
  }, []);
  const handleChangeShipmentCreateEndDate = useCallback((newValue) => {
    setShipmentCreateEndDate(newValue);
    setToggleData(true);
    setSearchLoading(true);
  }, []);

  const filters = [
    {
      key: "orderCreateDate",
      label: "Order create date",
      filter: (
        <>
          <ChoiceList
            title="Order create date"
            titleHidden
            choices={dateOptions}
            selected={orderCreateDate || []}
            onChange={handleOrderCreateDateChange}
          />
          {orderCreateDate?.includes("Custom") && (
            <FormLayout>
              <LegacyStack vertical>
                <LegacyStack.Item>
                  <TextField
                    type="date"
                    label="Starting"
                    value={orderCreateStartDate}
                    onChange={handleChangeOrderCreateStartDate}
                    autoComplete="off"
                  />
                </LegacyStack.Item>
                <LegacyStack.Item>
                  <TextField type="date" label="Ending" value={orderCreateEndDate} onChange={handleChangeOrderCreateEndDate} autoComplete="off" />
                </LegacyStack.Item>
              </LegacyStack>
            </FormLayout>
          )}
        </>
      ),
      shortcut: true,
    },
    {
      key: "shipmentCreateDate",
      label: "Shipment create date",
      filter: (
        <>
          <ChoiceList
            title="Shipment create date"
            titleHidden
            choices={dateOptions}
            selected={shipmentCreateDate || []}
            onChange={handleShipmentCreateDateChange}
          />
          {shipmentCreateDate?.includes("Custom") && (
            <FormLayout>
              <LegacyStack vertical>
                <LegacyStack.Item>
                  <TextField
                    type="date"
                    label="Starting"
                    value={shipmentCreateStartDate}
                    onChange={handleChangeShipmentCreateStartDate}
                    autoComplete="off"
                  />
                </LegacyStack.Item>
                <LegacyStack.Item>
                  <TextField
                    type="date"
                    label="Ending"
                    value={shipmentCreateEndDate}
                    onChange={handleChangeShipmentCreateEndDate}
                    autoComplete="off"
                  />
                </LegacyStack.Item>
              </LegacyStack>
            </FormLayout>
          )}
        </>
      ),
      shortcut: true,
    },
    {
      key: "carrier",
      label: "Carrier",
      filter: (
        <ChoiceList
          title="Order create date"
          titleHidden
          choices={carriersOptions}
          selected={carrier || []}
          onChange={handleCarrierChange}
          allowMultiple
        />
      ),
      shortcut: true,
    },
    {
      key: "shipmentStatus",
      label: "Shipment status",
      filter: (
        <ChoiceList
          title="Shipment status"
          titleHidden
          choices={shipmentStatusOptions}
          selected={shipmentStatus || []}
          onChange={handleShipmentStatusChange}
          allowMultiple
        />
      ),
      shortcut: true,
    },
    {
      key: "fulfillmentStatus",
      label: "Fulfillment status",
      filter: (
        <ChoiceList
          title="Fulfillment status"
          titleHidden
          choices={[
            { label: "Fulfilled", value: "Fulfilled" },
            { label: "Unfulfilled", value: "Unfulfilled" },
          ]}
          selected={fulfillmentStatus || []}
          onChange={handleFulfillmentStatusChange}
        />
      ),
      shortcut: true,
    },
    {
      key: "transitTime",
      label: "Transit time",
      filter: (
        <ChoiceList
          title="Transit time"
          titleHidden
          choices={[
            { label: "Fast (1 - 5)", value: "Fast (1 - 5)" },
            { label: "Normal (6 - 11)", value: "Normal (6 - 11)" },
            { label: "Slow (12 - 20)", value: "Slow (12 - 20)" },
            { label: "Very Slow (20~)", value: "Very Slow (20~)" },
          ]}
          selected={transitTime || []}
          onChange={handleTransitTimeChange}
          allowMultiple
        />
      ),
      shortcut: true,
    },
    {
      key: "notes",
      label: "Note",
      filter: (
        <ChoiceList
          title="Note"
          titleHidden
          choices={[
            { label: "With note", value: "With note" },
            { label: "Without note", value: "Without note" },
          ]}
          selected={notes || []}
          onChange={handleNotesChange}
          allowMultiple
        />
      ),
      shortcut: true,
    },
    {
      key: "origin",
      label: "Origin",
      filter: <ChoiceList title="Origin" titleHidden choices={originOptions} selected={origin || []} onChange={handleOriginChange} allowMultiple />,
      shortcut: true,
    },
    {
      key: "destination",
      label: "Destination",
      filter: (
        <ChoiceList
          title="Destination"
          titleHidden
          choices={destinationsOptions}
          selected={destination || []}
          onChange={handleDestinationChange}
          allowMultiple
        />
      ),
      shortcut: true,
    },
  ];

  const appliedFilters = [];
  if (orderCreateDate && !isEmpty(orderCreateDate)) {
    const key = "orderCreateDate";
    appliedFilters.push({
      key,
      label: disambiguateLabel(key, orderCreateDate),
      onRemove: handleOrderCreateDateRemove,
    });
  }
  if (shipmentCreateDate && !isEmpty(shipmentCreateDate)) {
    const key = "shipmentCreateDate";
    appliedFilters.push({
      key,
      label: disambiguateLabel(key, shipmentCreateDate),
      onRemove: handleShipmentCreateDateRemove,
    });
  }
  if (carrier && !isEmpty(carrier)) {
    const key = "carrier";
    appliedFilters.push({
      key,
      label: disambiguateLabel(key, carrier),
      onRemove: handleCarrierRemove,
    });
  }
  if (shipmentStatus && !isEmpty(shipmentStatus)) {
    const key = "shipmentStatus";
    appliedFilters.push({
      key,
      label: disambiguateLabel(key, shipmentStatus),
      onRemove: handleShipmentStatusRemove,
    });
  }
  if (fulfillmentStatus && !isEmpty(fulfillmentStatus)) {
    const key = "fulfillmentStatus";
    appliedFilters.push({
      key,
      label: disambiguateLabel(key, fulfillmentStatus),
      onRemove: handleFulfillmentStatusRemove,
    });
  }
  if (transitTime && !isEmpty(transitTime)) {
    const key = "transitTime";
    appliedFilters.push({
      key,
      label: disambiguateLabel(key, transitTime),
      onRemove: handleTransitTimeRemove,
    });
  }
  if (notes && !isEmpty(notes)) {
    const key = "notes";
    appliedFilters.push({
      key,
      label: disambiguateLabel(key, notes),
      onRemove: handleNotesRemove,
    });
  }
  if (origin && !isEmpty(origin)) {
    const key = "origin";
    appliedFilters.push({
      key,
      label: disambiguateLabel(key, origin),
      onRemove: handleOriginRemove,
    });
  }
  if (destination && !isEmpty(destination)) {
    const key = "destination";
    appliedFilters.push({
      key,
      label: disambiguateLabel(key, destination),
      onRemove: handleDestinationRemove,
    });
  }

  const resourceName = {
    singular: "shipment",
    plural: "shipments",
  };

  const { selectedResources, allResourcesSelected, handleSelectionChange } = useIndexResourceState(fulfillments);

  const formatDate = (isoDateString) => {
    const date = new Date(isoDateString);
    return new Intl.DateTimeFormat("en-US", {
      month: "long",
      day: "2-digit",
      year: "numeric",
    }).format(date);
  };

  const rowMarkup = fulfillments?.map(
    (
      {
        id,
        tracking_number,
        order,
        tracking_company,
        shipment_status,
        shipment_last_event,
        created_at,
        track_info,
        carrier_name_base,
        carrier_code_base,
      },
      index,
    ) => {
      const handleSelectionChange = (id) => {
        const target = event.target;
        const isAttribute = target.getAttribute("aria-label");

        if (isAttribute !== "order_number" && isAttribute !== "tracking_number") {
          navigate(`/orders/${id}`);
          event.stopPropagation(); // Prevent row from being selected
        } else if (isAttribute === "order_number") {
          window.open(`https://${shop}/admin/orders/${order?.shopify_order_id}`, "_blank"); // Open link in a new window
        } else {
          console.log("target", target);
          console.log("isAttribute", isAttribute);
        }
      };
      return (
        <IndexTable.Row id={id} key={id} selected={selectedResources.includes(id)} position={index} onClick={() => handleSelectionChange(id)}>
          <IndexTable.Cell className="order_container">
            <div className="TableCell__Content row-show">
              <LegacyStack vertical spacing="none">
                <LegacyStack.Item>
                  <div className="order-number-text">
                    <Text as="span" variant="bodyMd">
                      {order?.name}
                    </Text>
                  </div>
                </LegacyStack.Item>
              </LegacyStack>
            </div>
            <div className="TableCell__Content row-hidden">
              <LegacyStack vertical spacing="none">
                <LegacyStack.Item>
                  <LegacyStack wrap={false}>
                    <LegacyStack.Item>
                      <div className="flex items-center justify-start gap-1 min-w-0">
                        <div className="order-number-text">
                          <Link
                              accessibilityLabel="order_number"
                              monochrome
                              removeUnderline
                              url={`https://${shop}/admin/orders/${order?.shopify_order_id}`}
                              target="_blank"
                          >
                            {order?.name}
                          </Link>
                        </div>
                        <Icon tone="subdued" source={ExternalSmallIcon} />
                      </div>
                    </LegacyStack.Item>
                  </LegacyStack>
                </LegacyStack.Item>
              </LegacyStack>
            </div>
          </IndexTable.Cell>
          <IndexTable.Cell className="tracking_container">
            <span className="tracking-number-text">{tracking_number || "-"}</span>
          </IndexTable.Cell>
          <IndexTable.Cell className="carrier_container">
            <span className="cell-ellipsis">{tracking_company ? tracking_company : "-"}</span>
          </IndexTable.Cell>
          <IndexTable.Cell className="status_container">
              <div className={`shipStatus-${selectBadgeTone(shipment_status)}`}>
                <Badge progress="complete">
                  {capitalizeWords(shipment_status) === "Notfound" || capitalizeWords(shipment_status) == null
                      ? "Pending"
                      : capitalizeWords(shipment_status)}
                </Badge>
              </div>
          </IndexTable.Cell>
          <IndexTable.Cell className="event_container">
            <span className="cell-ellipsis">{shipment_last_event ? shipment_last_event : "No info"}</span>
          </IndexTable.Cell>
          <IndexTable.Cell className="date_container">
            <span className="cell-ellipsis">{formatDate(created_at)}</span>
          </IndexTable.Cell>
          <IndexTable.Cell className="date_container">
            <span className="cell-ellipsis">{formatDate(order?.created_at)}</span>
          </IndexTable.Cell>
        </IndexTable.Row>
      );
    },
  );

  const syncOrders = async (value, key) => {
    let sessionToken = await getSessionToken(appBridge);
    setSearchLoading(true);

    try {
      const response = await axios.get(`${apiUrl}sync_orders?specific_date=${value}`, {
        headers: {
          Authorization: `Bearer ${sessionToken}`,
        },
      });

      if (response.data?.status == "success") {
        setSuccessToast(true);
        setToastMsg(response?.data?.message);
        setToggleData(true);
      } else {
        setErrorToast(true);
        setToastMsg(response?.data?.message);
      }
    } catch (error) {
      console.error("Error updating widget status", error);
    }
  };

  const promotedBulkActions = [
    {
      content: "Update carrier",
      onAction: () => console.log("Todo: implement create shipping labels"),
    },
    {
      content: "Update status",
      onAction: () => console.log("Todo: implement mark as fulfilled"),
    },
  ];
  /*useEffect(() => {
    const status = searchParams.get('status');
    if (status) {
      if (status === "Pending") {
        setSelected(1);
      } else if (status === "In transit") {
        setSelected(2);
      }  else if (status === "Pickup") {
        setSelected(3);
      } else if (status === "Delivered") {
        setSelected(4);
      } else if (status === "Out for delivery") {
        setSelected(5);
      } else if (status === "Exception") {
        setSelected(6);
      } else if (status === "Expired") {
        setSelected(7);
      }
      // searchParams.delete('status');
      // setSearchParams(searchParams);
      // fetchData();
    }

  }, [searchParams, setSearchParams]);*/

  const getParamsStatus = () => {
    const status = searchParams.get('status');
    if (status) {
      setLoading(true);
      setSearchLoading(true);
      if (status === "Pending") {
        setSelected(1);
      } else if (status === "Info Received") {
          setSelected(2);
      } else if (status === "In transit") {
        setSelected(3);
      } else if (status === "Pickup") {
        setSelected(4);
      } else if (status === "Delivered") {
        setSelected(5);
      } else if (status === "Out for delivery") {
        setSelected(6);
      } else if (status === "Exception") {
        setSelected(7);
      } else if (status === "Expired") {
        setSelected(8);
      }else{
          setSelected(0);
      }
      searchParams.delete('status');
      setSearchParams(searchParams);
      setToggleData(true);
    }
  }
  return loading ? (
    <TableSkeletonWithTabs
      primaryAction={true}
      length="10"
      fullWidth={false}
      SkeletonTabsLeft={true}
      thumbnail={true}
      checkbox={false}
      SkeletonTabsLeftLength={3}
    />
  ) : (
    <Page
      // fullWidth
      title="Orders"
      actionGroups={[
        {
          title: "Sync Orders",
          actions: [
            {
              content: "Today",
              onAction: () => syncOrders("Today", "Sync Orders"),
            },
            {
              content: "Last 7 days",
              onAction: () => syncOrders("Last 7 days", "Sync Orders"),
            },
            {
              content: "Last 15 days",
              onAction: () => syncOrders("Last 15 days", "Sync Orders"),
            },
            {
              content: "Last 30 days",
              onAction: () => syncOrders("Last 30 days", "Sync Orders"),
            },
            {
              content: "Last 90 days",
              onAction: () => syncOrders("Last 90 days", "Sync Orders"),
            },
          ],
        },
      ]}
    >
      <Layout>
        <Layout.Section>
          <LegacyCard>
            <IndexFilters
              loading={searchLoading}
              queryValue={queryValue}
              queryPlaceholder="Searching in all"
              onQueryChange={handleFiltersQueryChange}
              onQueryClear={() => {
                setQueryValue("");
                setToggleData(true);
                setSearchLoading(true);
              }}
              cancelAction={{
                onAction: onHandleCancel,
                disabled: false,
                loading: false,
              }}
              tabs={tabs}
              selected={selected}
              onSelect={setSelected}
              canCreateNewView={false}
              onCreateNewView={onCreateNewView}
              filters={filters}
              appliedFilters={appliedFilters}
              onClearAll={handleFiltersClearAll}
              mode={mode}
              setMode={setMode}
            />
            <div className="OrderTable">
              <IndexTable
                resourceName={resourceName}
                itemCount={fulfillments?.length}
                selectable={false}
                headings={[
                  { title: "Order" },
                  { title: "Tracking number" },
                  // { title: "Tracking number Extra Column", hidden: true },
                  { title: "Carrier" },
                  { title: "Status" },
                  { title: "Latest event" },
                  { title: "Shipped date" },
                  { title: "Order date" },
                ]}
                pagination={{
                  hasPrevious: hasPreviousPage,
                  onPrevious: () => handlePagination("prev"),
                  hasNext: hasNextPage,
                  onNext: () => handlePagination("next"),
                }}
                promotedBulkActions={promotedBulkActions}
              >
                {rowMarkup}
              </IndexTable>
            </div>
          </LegacyCard>
        </Layout.Section>
        <Layout.Section></Layout.Section>
      </Layout>
      {toastErrorMsg}
      {toastSuccessMsg}
    </Page>
  );
}

function disambiguateLabel(key, value) {
  switch (key) {
    case "moneySpent":
      return `Money spent is between $${value[0]} and $${value[1]}`;
    case "taggedWith":
      return `Tagged with ${value}`;
    case "orderCreateDate":
      return `Order created: ${value.map((val) => val).join(", ")}`;
    case "shipmentCreateDate":
      return `Shipment created: ${value.map((val) => val).join(", ")}`;
    case "carrier":
      return `${value?.length > 1 ? "Carriers" : "Carrier"} ${value.map((val) => val).join(", ")}`;
    case "shipmentStatus":
      return `Shipment Status: ${value.map((val) => val).join(", ")}`;
    case "fulfillmentStatus":
      return `Fulfillment Status: ${value.map((val) => val).join(", ")}`;
    case "transitTime":
      return `Transit time: ${value.map((val) => val).join(", ")}`;
    case "notes":
      return `${value?.length > 1 ? "Notes" : "Note"} ${value.map((val) => val).join(", ")}`;
    case "origin":
      return `${value?.length > 1 ? "Origins" : "Origin"} ${value.map((val) => val).join(", ")}`;
    case "destination":
      return `${value?.length > 1 ? "Destinations" : "Destination"} ${value.map((val) => val).join(", ")}`;

    default:
      return value;
  }
}

function isEmpty(value) {
  if (Array.isArray(value)) {
    return value.length === 0;
  } else {
    return value === "" || value == null;
  }
}
