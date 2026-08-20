import { Card, EmptyState, Page } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import  notFoundImage from "../assets/empty-state.svg";

export default function NotFound() {
  const { t } = useTranslation();
  return (
    <Page>
      <Card>
        <Card.Section>
          <EmptyState heading={t("NotFound.heading")} image={notFoundImage}>
            <p>{t("NotFound.description")}</p>
          </EmptyState>
        </Card.Section>
      </Card>
    </Page>
  );
}
