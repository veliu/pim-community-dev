import React, {useState} from 'react';
import {CreateGeneratorModal, CreateGeneratorPage} from '../pages/';
import {IdentifierGenerator, PROPERTY_NAMES} from '../models';
import {ListPage} from '../pages/ListPage';
import {GeneratorEditProvider} from '../context/GeneratorEditProvider';

enum Screen {
  LIST,
  CREATE_MODAL,
  CREATE_PAGE,
}

const List: React.FC = () => {
  const [currentScreen, setCurrentScreen] = useState<Screen>(Screen.LIST);

  const openModal = () => setCurrentScreen(Screen.CREATE_MODAL);
  const closeModal = () => setCurrentScreen(Screen.LIST);
  const openCreatePage = () => {
    setCurrentScreen(Screen.CREATE_PAGE);
  };

  const initialGenerator: IdentifierGenerator = {
    code: '',
    labels: {},
    target: '',
    structure: [{type: PROPERTY_NAMES.FREE_TEXT, string: 'AKN'}],
    delimiter: '',
    conditions: []
  };

  return (
    <>
      {currentScreen === Screen.LIST && <ListPage onCreate={openModal} />}
      <GeneratorEditProvider initialGenerator={initialGenerator}>
        {currentScreen === Screen.CREATE_MODAL && <CreateGeneratorModal onClose={closeModal} onSave={openCreatePage} />}
        {currentScreen === Screen.CREATE_PAGE && <CreateGeneratorPage />}
      </GeneratorEditProvider>
    </>
  );
};

export {List};
