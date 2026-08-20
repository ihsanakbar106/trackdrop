import {
  ActionList,
  Badge,
  BlockStack,Tabs,
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
import OrderToDeliveryTime from "./order_to_delivery_time.jsx";
import TransitTime from "./transit_time.jsx";
import TrackingPage from "./tracking_page.jsx";
import ExceptionPage from "./exception.jsx";
import {useNavigate} from "react-router-dom";

export default function Analytics() {
  const appBridge = useAppBridge();
  const { apiUrl } = useContext(AppContext);
  const [selected, setSelected] = useState(0);
  const navigate = useNavigate();

  const handleTabChange = useCallback(
      (selectedTabIndex) => setSelected(selectedTabIndex),
      [],
  );
  const tabs = [
    {
      id: 'order-to-delivery-time',
      content: 'Order-to-delivery time',
      panelID: 'order-to-delivery-time',
    },
    {
      id: 'transit-time',
      content: 'Transit time',
      panelID: 'transit-time',
    },
    {
      id: 'tracking-page',
      content: 'Tracking page',
      panelID: 'tracking-page',
    },
    {
      id: 'exceptions',
      content: 'Exceptions',
      panelID: 'exceptions',
    },
  ];
  const fetchData = async () => {
    try {
      const sessionToken = await getSessionToken(appBridge);
      const response = await axios.get(
          `${apiUrl}checkPlan`,
          {
            headers: {
              Authorization: `Bearer ${sessionToken}`,
            },
          },
      );
      // console.log('Responce',response?.data);
      const { plan_id} = response?.data;
      if(!plan_id){
        navigate("/billing");
      }


      // console.log("totalShipments", totalShipments);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {

    }
  };

  useEffect(() => {
      fetchData();
  }, []);
  return (
    <Page
      title="Analytics"

    >
      <Layout>
        <Layout.Section variant="fullWidth">
          <BlockStack gap={"400"}>
            <Card padding={0}>
            <Tabs tabs={tabs} selected={selected} onSelect={handleTabChange}>
            </Tabs>
            </Card>
            {selected === 0 && <OrderToDeliveryTime />}
            {selected === 1 && <TransitTime />}
            {selected === 2 && <TrackingPage />}
            {selected === 3 && <ExceptionPage />}
          </BlockStack>
        </Layout.Section>
        <Layout.Section></Layout.Section>
        <Layout.Section></Layout.Section>
      </Layout>
    </Page>
  );
}
