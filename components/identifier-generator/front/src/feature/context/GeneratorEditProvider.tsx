import React, {createContext, ReactNode, useState} from 'react';
import {IdentifierGenerator} from '../models';
import {useIdentifierGeneratorContext} from './useIdentifierGeneratorContext';

type GeneratorEditProviderProps = {
  children: ReactNode,
  initialGenerator: IdentifierGenerator
}

type GeneratorEditContextProps = {
  generator: IdentifierGenerator,
  setGenerator: (generator: IdentifierGenerator) => void
};

const GeneratorEditContext = createContext<GeneratorEditContextProps>({
  generator: {
    conditions: [],
    structure: [],
    target: '',
    labels: {},
    code: '',
    delimiter: ''
  },
  setGenerator: () => {
    return null;
  }
});

const GeneratorEditProvider: React.FC<GeneratorEditProviderProps> = ({children, initialGenerator}) => {
  const [generator, setGenerator] = useState(initialGenerator);
  const identifierGeneratorContext = useIdentifierGeneratorContext();

  const onChange = (generator: IdentifierGenerator) => {
    if (JSON.stringify(generator) !== JSON.stringify(initialGenerator)) {
      identifierGeneratorContext.unsavedChanges.setHasUnsavedChanges(true);
    }
    setGenerator(generator);
  };

  return (
    <GeneratorEditContext.Provider value={{generator, setGenerator: onChange}}>
      {children}
    </GeneratorEditContext.Provider>
  );
};

export {GeneratorEditProvider, GeneratorEditContext};
export type { GeneratorEditContextProps };

