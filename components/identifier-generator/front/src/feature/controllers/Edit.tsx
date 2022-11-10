import React, {useEffect} from 'react';
import {useParams} from 'react-router-dom';
import {EditGeneratorPage} from '../pages/';
import {useGetIdentifierGenerator} from '../hooks';
import {LoaderIcon, Placeholder, ServerErrorIllustration} from 'akeneo-design-system';
import {IdentifierGeneratorNotFound} from '../errors';
import {Styled} from '../components/Styled';
import {useTranslate} from '@akeneo-pim-community/shared';
import {GeneratorEditProvider} from '../context/GeneratorEditProvider';
import {useIdentifierGeneratorContext} from '../context/useIdentifierGeneratorContext';

const Edit: React.FC = () => {
  const translate = useTranslate();
  const {identifierGeneratorCode} = useParams<{identifierGeneratorCode: string}>();
  const {data, error} = useGetIdentifierGenerator(identifierGeneratorCode);
  const {unsavedChanges} = useIdentifierGeneratorContext();

  useEffect(() => {
    return () => {
      unsavedChanges.setHasUnsavedChanges(false);
    };
  }, [unsavedChanges]);

  if (error) {
    let title = translate('pim_error.general');
    let subtitle = error?.message;

    if (error instanceof IdentifierGeneratorNotFound) {
      title = translate('pim_error.404');
      subtitle = translate('pim_error.identifier_generator_not_found');
    }

    return (
      <Styled.FullPageCenteredContent>
        <Placeholder illustration={<ServerErrorIllustration />} size="large" title={title}>
          {subtitle}
        </Placeholder>
      </Styled.FullPageCenteredContent>
    );
  }

  if (typeof data === 'undefined') {
    return (
      <Styled.FullPageCenteredContent>
        <LoaderIcon data-testid={'loadingIcon'} />
      </Styled.FullPageCenteredContent>
    );
  }

  return (
    <GeneratorEditProvider initialGenerator={data}>
      <EditGeneratorPage />
    </GeneratorEditProvider>
  );
};

export {Edit};
