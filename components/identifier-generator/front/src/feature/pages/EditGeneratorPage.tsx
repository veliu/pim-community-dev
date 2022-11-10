import React, {useMemo} from 'react';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {useSaveGenerator} from '../hooks/useSaveGenerator';
import {Prompt} from 'react-router-dom';
import {useGeneratorEditContext} from '../context/useGeneratorEditContext';
import {useGetIdentifierGenerator} from '../hooks';

const EditGeneratorPage: React.FC = () => {
  const {save, isLoading, error} = useSaveGenerator();
  const {generator} = useGeneratorEditContext();
  const {data} = useGetIdentifierGenerator(generator.code);

  const isBlocking = useMemo(() => {
    return JSON.stringify(generator) !== JSON.stringify(data);
  }, [data, generator]);

  return (
    <>
      <Prompt
        when={isBlocking}
        message={location =>
          `Are you sure you want to go to ${location.pathname}`
        }
      />
      <CreateOrEditGeneratorPage
        mainButtonCallback={save}
        isMainButtonDisabled={isLoading}
        validationErrors={error}
        isNew={false}
      />
    </>
  );
};

export {EditGeneratorPage};
