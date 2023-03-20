import React, {FC, useCallback} from 'react';
import {SelectChannelDropdown} from './SelectChannelDropdown';
import {SelectLocaleDropdown} from './SelectLocaleDropdown';
import {SelectChannelLocaleDropdown} from './SelectChannelLocaleDropdown';
import {Source} from '../../models/Source';
import {Attribute} from '../../../../models/Attribute';
import {SourceErrors} from '../../models/SourceErrors';
import {AssetAttributeSourceSelection} from './AssetAttributeSourceSelection/AssetAttributeSourceSelection';
import {Target} from '../../models/Target';

type Props = {
    source: Source;
    target: Target;
    attribute: Attribute;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
};

export const SourceSettings: FC<Props> = ({source, target,attribute, errors, onChange}) => {
    const onChangeMiddleware = useCallback(
        source => {
            if (
                (attribute.type === 'pim_catalog_simpleselect' ||
                    attribute.type === 'pim_catalog_multiselect' ||
                    source.source === 'categories') &&
                (undefined === source.parameters?.label_locale || null === source.parameters?.label_locale)
            ) {
                source = {...source, parameters: {...source.parameters, label_locale: source.locale ?? null}};
            }

            if (attribute?.type === 'pim_catalog_price_collection' && !(source.parameters.currency ?? false)) {
                source = {...source, parameters: {...source.parameters, currency: source.currency ?? null}};
            }

            if (attribute?.type === 'pim_catalog_metric') {
                source = {...source, parameters: {...source.parameters, unit: attribute.default_measurement_unit}};
            }

            onChange(source);
        },
        [onChange, attribute]
    );
    return (
        <>
            {attribute.scopable && (
                <SelectChannelDropdown source={source} onChange={onChangeMiddleware} error={errors?.scope} />
            )}
            {attribute.localizable && !attribute.scopable && (
                <SelectLocaleDropdown source={source} onChange={onChangeMiddleware} error={errors?.locale} />
            )}
            {attribute.localizable && attribute.scopable && (
                <SelectChannelLocaleDropdown
                    source={source}
                    onChange={onChangeMiddleware}
                    error={errors?.locale}
                    disabled={source.scope === null}
                />
            )}
            {attribute.asset_family && <AssetAttributeSourceSelection
                source={source}
                target={target}
                onChange={onChangeMiddleware}
                errors={errors}
                assetFamilyIdentifier={attribute.asset_family}
            />}
        </>
    );
};
