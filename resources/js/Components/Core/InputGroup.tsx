import React from 'react';
// @ts-ignore
import {InertiaFormProps} from "@inertiajs/react/types/useForm";
import InputLabel from "@/Components/Core/InputLabel";
import TextInput from "@/Components/Core/TextInput";
import InputError from "@/Components/Core/InputError";
import Checkbox from "@/Components/Core/Checkbox";
import TextAreaInput from "@/Components/Core/TextAreaInput";

function InputGroup(
  {
    form,
    label,
    field,
    required = false,
    type = 'text',
    className = '',
    placeholder = '',
  }: {
    form: InertiaFormProps,
    label: string,
    field: string,
    required?: boolean,
    type?: string,
    className?: string,
    placeholder?: string,
  }) {
  return (
    <div className={className}>
      {type === 'checkbox' && (
        <>
          <label className="flex items-center">
            <Checkbox
              name={field}
              checked={form.data[field]}
              onChange={(e) =>
                form.setData(field, e.target.checked)
              }
            />
            <span className="ms-2 text-sm text-gray-600 dark:text-gray-400">
                {label} {required && <span className="text-error">*</span>}
            </span>
          </label>

        </>
      )}
      {type !== 'checkbox' && (
        <>
          <InputLabel htmlFor={field}>
            {label} {required && <span className="text-error">*</span>}
          </InputLabel>

          {type !== 'textarea' && <TextInput
            id={field}
            type={type}
            name={field}
            value={form.data[field]}
            className={'mt-1 block w-full ' + (form.errors[field] ? 'input-error' : '')}
            placeholder={placeholder}
            onChange={(e) => form.setData(field, e.target.value)}
          />
          }
          {type === 'textarea' && (
            <TextAreaInput
              id={field}
              name={field}
              rows={3}
              value={form.data[field]}
              className={"mt-1 block w-full " + (form.errors[field] ? 'textarea-error' : '')}
              placeholder={placeholder}
              onChange={(e) => form.setData(field, e.target.value)}
            />
          )}
        </>
      )}

      <InputError message={form.errors[field]} className="mt-2"/>
    </div>
  );
}

export default InputGroup;
