import {FetchStatus, useFetch, useRoute} from '@akeneo-pim-community/shared';
import {LocaleCollection} from "../models";

type CatalogActivatedLocalesResponse = {
  load: () => Promise<void>;
  status: FetchStatus;
  localeCodes: LocaleCollection | null;
  error: string | null;
};

const useCatalogActivatedLocales = (): CatalogActivatedLocalesResponse => {
  const url = useRoute('internal_api_category_catalog_activated_locales', {});
  const [localeCodes, load, status, error] = useFetch<LocaleCollection>(url);
  return {load, localeCodes, status, error};
};

export {useCatalogActivatedLocales};
export type {CatalogActivatedLocalesResponse};
