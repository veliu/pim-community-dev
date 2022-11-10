import React, {useCallback} from 'react';
import {LabelCollection} from '../models';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import {IdentifierAttributeSelector, LabelTranslations} from '../components';
import {Styled} from '../components/Styled';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useGeneratorEditContext} from '../context/useGeneratorEditContext';

const GeneralPropertiesTab: React.FC = () => {
  const translate = useTranslate();
  const {generator, setGenerator} = useGeneratorEditContext();

  const onLabelChange = useCallback(
    (labels: LabelCollection) => {
      setGenerator({...generator, labels: labels});
    },
    [generator, setGenerator]
  );

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_identifier_generator.general.title')}</SectionTitle.Title>
      </SectionTitle>
      <Styled.FormContainer>
        <Field label={'pim_common.code'}>
          <TextInput value={generator.code} readOnly={true} />
        </Field>
        <IdentifierAttributeSelector code={generator.target || ''} />
      </Styled.FormContainer>
      <LabelTranslations labelCollection={generator.labels} onLabelsChange={onLabelChange} />
    </>
  );
};

export {GeneralPropertiesTab};
