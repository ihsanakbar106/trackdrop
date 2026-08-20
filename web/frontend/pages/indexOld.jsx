import {
    Button,
    ButtonGroup,
    Grid,
    Icon,
    Layout,
    LegacyCard,
    LegacyStack,
    Page,
    useIndexResourceState,
    SkeletonBodyText,
    SkeletonDisplayText,
    SkeletonPage,
    Spinner,
    Text,
    TextContainer,
    Toast,
    EmptySearchResult,
} from "@shopify/polaris";
import axios from "axios";
import React, {useCallback, useContext, useEffect, useState} from "react";
import {useNavigate} from "react-router-dom";
import {getSessionToken} from "@shopify/app-bridge-utils";
import {useAppBridge} from "@shopify/app-bridge-react";
import {AppContext, AlertBanner} from "../components";

export default function AddWidget() {
    const navigate = useNavigate();
    const appBridge = useAppBridge();
    const {apiUrl} = useContext(AppContext);
    const [sessionData, setSessionData] = useState('');
    const [allWidgetData, setAllWidgetData] = useState(null);
    const [shopWidgetData, setShopWidgetData] = useState(null);
    const [storeStatus, setStoreStatus] = useState(false);
    const [toggleLoadData, setToggleLoadData] = useState(true);
    const [errorToast, setErrorToast] = useState(false);
    const [successToast, setSuccessToast] = useState(false);
    const [toastMsg, setToastMsg] = useState("");
    const [loading, setLoading] = useState(true);
    const [loadingState, setLoadingState] = useState(false);
    const [btnLoading, setBtnLoading] = useState(false);

    const toggleErrorMsgActive = useCallback(() => setErrorToast((errorToast) => !errorToast), []);
    const toggleSuccessMsgActive = useCallback(() => setSuccessToast((successToast) => !successToast), []);

    const toastErrorMsg = errorToast ? (<Toast content={toastMsg} error onDismiss={toggleErrorMsgActive}/>) : null;

    const toastSuccessMsg = successToast ? (<Toast content={toastMsg} onDismiss={toggleSuccessMsgActive}/>) : null;

    const handleCheckboxChangeIsWidgetActive = async (index, widgetId, value) => {
        let sessionToken = await getSessionToken(appBridge);
        let enableValue = "";
        setBtnLoading((prev) => {
            let toggleId;
            if (prev[index]) {
                toggleId = {[index]: false};
            } else {
                toggleId = {[index]: true};
            }
            return {...toggleId};
        });

        if (value == 0) {
            enableValue = 1;
        } else {
            enableValue = 0;
        }
        try {
            const response = await axios.post(`${apiUrl}active-widget/${widgetId}`, {
                status: enableValue,
            }, {
                headers: {
                    Authorization: `Bearer ${sessionToken}`,
                },
            });
            if (response.data?.status == "success") {
                setSuccessToast(true);
                setToastMsg(response?.data?.message);
                setToggleLoadData(true);
            }
        } catch (error) {
            setErrorToast(true);
            setToastMsg(error?.data?.message);
            setBtnLoading(false);
            console.error("Error updating widget status", error);
        }
    };

    const handleEnableWidget = async (index, widgetId) => {
        let sessionToken = await getSessionToken(appBridge);
        setBtnLoading((prev) => {
            let toggleId;
            if (prev[index]) {
                toggleId = {[index]: false};
            } else {
                toggleId = {[index]: true};
            }
            return {...toggleId};
        });

        try {
            const response = await axios.get(`${apiUrl}enable-widget/${widgetId}`, {
                headers: {
                    Authorization: `Bearer ${sessionToken}`,
                },
            });
            if (response?.data?.status == "success") {
                setSuccessToast(true);
                setToastMsg(response?.data?.message);
                setToggleLoadData(true);

            }
        } catch (error) {
            setBtnLoading(false);
            console.error("Error updating widget status", error);
        }
    };

    function convertNumberToBoolean(value) {
        let booleanValue;
        if (value === 1) {
            booleanValue = true;
        } else {
            booleanValue = false;
        }
        return booleanValue;
    }

    const generateSkeletonCells = (count) => {
        const cells = [];
        for (let i = 0; i < count; i++) {
            cells.push(<Grid.Cell
                key={i}
                columnSpan={{
                    xs: 6, sm: 3, md: 3, lg: 4, xl: 4,
                }}
            >
                <LegacyCard>
                    <LegacyCard.Section>
                        <TextContainer>
                            <SkeletonDisplayText size="small"/>
                            <SkeletonBodyText/>
                        </TextContainer>
                    </LegacyCard.Section>
                </LegacyCard>
            </Grid.Cell>);
        }
        return cells;
    };


    // pagination states
    const [shopData, setShopData] = useState('');
    const [hasNextPage, setHasNextPage] = useState(false);
    const [hasPreviousPage, setHasPreviousPage] = useState(false);
    const [pageCursor, setPageCursor] = useState("next");
    const [pageCursorValue, setPageCursorValue] = useState("");
    const [nextPageCursor, setNextPageCursor] = useState("");
    const [previousPageCursor, setPreviousPageCursor] = useState("");
    const [feedbacksLoading, setFeedbacksLoading] = useState(true);
    const [feedbacks, setFeedbacks] = useState(true);

    const emptyStateMarkup = (<EmptySearchResult title={"No feedback found"} withIllustration/>);


    const handlePagination = (value) => {
        // console.log("value", value, nextPageCursor)
        if (value === "next") {
            setPageCursorValue(nextPageCursor);
        } else {
            setPageCursorValue(previousPageCursor);
        }
        setPageCursor(value);
        setToggleLoadData(!toggleLoadData);
    };
    const getAllFeedbacksData = async () => {
        setFeedbacksLoading(true);
        try {
            if (pageCursorValue != '') {
                var url = pageCursorValue + '&search=' + queryValue;
            } else {
                var url = `${apiUrl}all-feedbacks?${pageCursor}=${pageCursorValue}&search=${queryValue}`;
            }

            let sessionToken = await getSessionToken(appBridge);
            const response = await axios.get(url, {
                headers: {
                    Authorization: `Bearer ${sessionToken}`,
                },
            });


            if (response?.data?.feedbacks?.data) {
                setFeedbacks(response?.data?.feedbacks?.data)
                setNextPageCursor(response?.data?.feedbacks?.next_page_url)
                setPreviousPageCursor(response?.data?.feedbacks?.prev_page_url)
                if (response?.data?.feedbacks?.next_page_url) {
                    setHasNextPage(true)
                } else {
                    setHasNextPage(false)
                }
                if (response?.data?.feedbacks?.prev_page_url) {
                    setHasPreviousPage(true)
                } else {
                    setHasPreviousPage(false)
                }
            }

            console.log("All feedbacks Data: ", response?.data)


            // setBtnLoading(false)
            // setToastMsg(response?.data?.message)
            // setSucessToast(true)


        } catch (error) {
            console.log("feedback api error:", error)
            // setToastMsg(error?.response?.data?.message)
            // setErrorToast(true)
        }
        setFeedbacksLoading(false);
    }

    const [queryValue, setQueryValue] = useState("");
    useEffect(() => {
        // console.log("toggleLoadData", toggleLoadData)
        getAllFeedbacksData()
    }, [toggleLoadData, queryValue]);

    const resourceName = {
        singular: "Feedback", plural: "Feedbacks",
    };
    const {
        selectedResources, allResourcesSelected, handleSelectionChange, removeSelectedResources
    } = useIndexResourceState(feedbacks);

    return (
        <div className="add-widget-page">
        {
            loading ? (
                <SkeletonPage>
                    <Grid>{generateSkeletonCells(8)}</Grid>
                </SkeletonPage>
            ) : (
                <Page title={'Feedbacks'}>
                    <div style={{marginBottom: '20px'}}>
                        <AlertBanner/>
                        <>
                            <IndexTable
                                resourceName={resourceName}
                                itemCount={feedbacks?.length}
                                hasMoreItems
                                selectable={true}
                                selectedItemsCount={allResourcesSelected ? "All" : selectedResources.length}
                                onSelectionChange={handleSelectionChange}
                                loading={feedbacksLoading}
                                emptyState={emptyStateMarkup}
                                headings={[{title: "Image"},
                                    {title: "Product Name"},
                                    {title: "SKU"}, {title: "Price"}, {title: "Quantity"}, {title: "Category"}, {title: "Status"}, {title: "Synced To Admin"}, {title: "Action"},]}
                            >
                                {feedbacks ? feedbacks?.map((feedback, index) => (<IndexTable.Row
                                    id={feedback?.id}
                                    key={feedback?.id}
                                    selected={selectedResources.includes(feedback?.id)}
                                    position={index}
                                    onClick={() => handleRowClick(feedback?.id)} // Add this line
                                >
                                    <IndexTable.Cell>
                                        <div className="feedback-image">
                                            <img
                                                src={feedback?.images[0]?.src}/>
                                        </div>
                                    </IndexTable.Cell>
                                    <IndexTable.Cell
                                        className="Capitalize-Cell">
                                        {feedback?.title != null ? feedback?.title : "---"}
                                    </IndexTable.Cell>
                                    <IndexTable.Cell
                                        className="Capitalize-Cell">
                                        {feedback?.sku != null ? feedback?.sku : "---"}
                                    </IndexTable.Cell>

                                    <IndexTable.Cell>{feedback?.feedbackPrice}</IndexTable.Cell>
                                    <IndexTable.Cell>{feedback?.feedbackQty}</IndexTable.Cell>
                                    <IndexTable.Cell>
                                        {feedback?.has_categories?.length > 0 ? (<>
                                            <Badge status='success'>
                                                {feedback?.has_categories.map((cat, index) => (
                                                    <span>{cat?.title}{feedback?.has_categories?.length - 1 === index ? '' : ' | '}</span>))}
                                            </Badge>
                                        </>) : ('---')}
                                    </IndexTable.Cell>
                                    <IndexTable.Cell>
                                        {feedback?.status == 'active' ? <Badge status='success'>
                                            Active
                                        </Badge> : <Badge status='destructive'>
                                            {feedback?.status}
                                        </Badge>}
                                    </IndexTable.Cell>
                                    <IndexTable.Cell>
                                        {feedback?.has_feedback_sync ? <Badge status='success'>
                                            Yes
                                        </Badge> : <Badge status='critical'>
                                            No
                                        </Badge>}
                                    </IndexTable.Cell>
                                    <IndexTable.Cell>
                                        <div>
                                            <img onClick={() => syncToAdminProducts(feedback?.shopify_id)}
                                                 style={{width: '40px', cursor: 'pointer'}} src={greenSync}
                                                 alt="logo"/>
                                        </div>

                                        {/*<Popover*/}
                                        {/*    active={active[feedback?.id]}*/}
                                        {/*    activator={<Button*/}
                                        {/*        onClick={() => toggleActive(feedback?.id)}*/}
                                        {/*        plain>*/}
                                        {/*        <Icon*/}
                                        {/*            source={HorizontalDotsMinor}></Icon>*/}
                                        {/*    </Button>}*/}
                                        {/*    autofocusTarget="first-node"*/}
                                        {/*    onClose={() => setActive(false)}*/}
                                        {/*>*/}
                                        {/*    <ActionList*/}
                                        {/*        actionRole="menuitem"*/}
                                        {/*        items={[{*/}
                                        {/*            content: "Edit",*/}
                                        {/*            onAction: () => handleEditAction(feedback?.id),*/}
                                        {/*        }, {*/}
                                        {/*            content: "View",*/}
                                        {/*            onAction: () => handleProductDetailAction(feedback?.id),*/}
                                        {/*        }, {*/}
                                        {/*            content: "Delete",*/}
                                        {/*            onAction: () => deleteProductModalHandler(feedback?.id),*/}
                                        {/*        },]}*/}
                                        {/*    />*/}
                                        {/*</Popover>*/}
                                    </IndexTable.Cell>
                                </IndexTable.Row>)) : <EmptySearchResult title={"No Product Found"}
                                                                         withIllustration/>}
                            </IndexTable>
                            <br/>
                            {hasPreviousPage === true || hasNextPage === true ? <div style={{textAlign: 'center'}}>
                                <Pagination
                                    hasPrevious={hasPreviousPage ? true : false}
                                    onPrevious={() => handlePagination("prev")}
                                    hasNext={hasNextPage ? true : false}
                                    onNext={() => handlePagination("next")}
                                />
                            </div> : ''}
                        </>
                        {toastErrorMsg}
                        {toastSuccessMsg}
                    </div>
                </Page>
            )
        }
        </div>
    )
}


