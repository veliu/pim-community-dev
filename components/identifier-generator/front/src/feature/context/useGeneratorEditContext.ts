import {useContext} from 'react';
import {GeneratorEditContext, GeneratorEditContextProps} from './GeneratorEditProvider';

const useGeneratorEditContext = (): GeneratorEditContextProps => useContext(GeneratorEditContext);

export {useGeneratorEditContext};
