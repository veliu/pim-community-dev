import React, {FC} from 'react';
import {Helper, Locale as LocaleLabel, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useUniqueEntitiesByCode} from '../../../../hooks/useUniqueEntitiesByCode';
import {useInfiniteLocales} from '../../../../hooks/useInfiniteLocales';
import {useLocalesByCodes} from '../../../../hooks/useLocalesByCodes';
import {Locale} from '../../../../models/Locale';

type Props = {
    locale: string | null;
    onChange: (locale: string) => void;
    error: string | undefined;
};

export const SelectLocaleDropdown: FC<Props> = ({locale, onChange, error}) => {
    const translate = useTranslate();
    const {data: selected} = useLocalesByCodes([locale ?? '']);
    const {data: results, fetchNextPage} = useInfiniteLocales();
    const locales = useUniqueEntitiesByCode<Locale>(selected, results);

    return (
        <>
            <SelectInput
                value={locale}
                onChange={onChange}
                onNextPage={fetchNextPage}
                clearable={false}
                invalid={error !== undefined}
                emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                openLabel={translate('akeneo_catalogs.common.select.open')}
                placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.locale.placeholder')}
                data-testid='source-parameter-locale-dropdown'
            >
                {locales?.map(locale => (
                    <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
                        <LocaleLabel code={locale.code} languageLabel={locale.label} />
                    </SelectInput.Option>
                ))}
            </SelectInput>
            {undefined !== error && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
        </>
    );
};
