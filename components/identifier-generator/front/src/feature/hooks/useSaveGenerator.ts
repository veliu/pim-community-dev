import {useMutation, useQueryClient} from 'react-query';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {IdentifierGenerator} from '../models';
import {useGeneratorEditContext} from '../context/useGeneratorEditContext';

type ErrorResponse = {
  path: string,
  message: string
}[];

type HookResponse = {
  isLoading: boolean,
  save: () => void,
  error: ErrorResponse
}

const useSaveGenerator = (): HookResponse => {
  const router = useRouter();
  const queryClient = useQueryClient();
  const notify = useNotify();
  const translate = useTranslate();
  const {generator} = useGeneratorEditContext();

  const callSave = async (generator: IdentifierGenerator) => {
    const res = await fetch(router.generate('akeneo_identifier_generator_rest_update', {code: generator.code}), {
      method: 'PATCH',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(generator)
    });
    const data = await res.json();

    return res.ok ? data : Promise.reject(data);
  };

  const {mutate, isLoading, error} = useMutation<IdentifierGenerator, ErrorResponse, IdentifierGenerator>(callSave);

  const save = () => mutate(generator, {
    onError: () => {
      notify(NotificationLevel.ERROR, translate('pim_identifier_generator.flash.create.error', {code: generator.code}));
    },
    onSuccess: () => {
      notify(NotificationLevel.SUCCESS, translate('pim_identifier_generator.flash.update.success', {code: generator.code}));
      queryClient.invalidateQueries({queryKey: ['getIdentifierGenerator']});
    },
  });

  return {isLoading, save, error: error ?? [] };
};

export {useSaveGenerator};
