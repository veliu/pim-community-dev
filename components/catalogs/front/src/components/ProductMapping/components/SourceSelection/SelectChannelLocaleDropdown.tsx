import React, {FC} from 'react';
import {Helper, Locale, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Source} from '../../models/Source';
import {useChannelLocales} from '../../../../hooks/useChannelLocales';
import styled from 'styled-components';

const Wrapper = styled.div`
    display: flex;
    flex-direction: column;
    flex: 1;
`;

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
    disabled: boolean;
};

export const SelectChannelLocaleDropdown: FC<Props> = ({source, onChange, error, disabled}) => {
    const translate = useTranslate();
    const {data: locales} = useChannelLocales(source.scope);

    return (
        <Wrapper>
            <SelectInput
                value={source.locale}
                onChange={newLocale => onChange({...source, locale: newLocale})}
                clearable={false}
                invalid={error !== undefined}
                emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                openLabel={translate('akeneo_catalogs.common.select.open')}
                placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.locale.placeholder')}
                data-testid='source-parameter-locale-dropdown'
                readOnly={disabled}
            >
                {locales?.map(locale => (
                    <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
                        <Locale code={locale.code} languageLabel={locale.label} />
                    </SelectInput.Option>
                ))}
            </SelectInput>
            {undefined !== error && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
        </Wrapper>
    );
};
