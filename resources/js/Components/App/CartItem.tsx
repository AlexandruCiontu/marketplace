import React, {useState} from 'react';
import {Link, router, useForm} from "@inertiajs/react";
import {CartItem as CartItemType} from "@/types";
import TextInput from "@/Components/Core/TextInput";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import {productRoute} from "@/helpers";

function CartItem({item}: { item: CartItemType }) {
  const deleteForm = useForm({
    option_ids: item.option_ids
  })

  const [quantity, setQuantity] = useState(item.quantity)
  const [error, setError] = useState('')

  const onDeleteClick = () => {
    deleteForm.delete(route('cart.destroy', item.product_id), {
      preserveScroll: true
    })
  }

  // Handle quantity change and immediately update the form
  const handleQuantityChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
    setError('')
    router.put(route('cart.update', item.product_id), {
      quantity: ev.target.value,
      option_ids: item.option_ids
    }, {
      preserveScroll: true,
      onError: (errors) => {
        setQuantity(item.quantity)
        setError(Object.values(errors)[0])
      }
    })
  };

  return (
    <>
      <div key={item.id} className="flex flex-col md:flex-row gap-6 p-3">
        <Link href={productRoute(item)}
              className="mx-auto w-full md:w-32 min-w-32 min-h-32 flex justify-center self-start">
          <img src={item.image} alt="" className="max-w-full max-h-full"/>
        </Link>
        <div className="flex-1 flex flex-col">
          <div className="flex-1">
            <h3 className="mb-3 text-sm font-semibold">
              <Link href={productRoute(item)}>
                {item.title}
              </Link>
            </h3>
            <div className="text-xs">
              {item.options.map(option => (
                <div key={option.id}>
                  <strong className="text-bold">{option.type.name}: </strong>
                  {option.name}
                </div>
              ))}
            </div>
          </div>
          <div className="grid grid-cols-2 sm:flex sm:grid-cols-4 gap-4 mt-4">
            <div className={"flex items-center gap-2 order-1"}>
              <div className="text-sm">Quantity:</div>
              <div className={error ? 'tooltip tooltip-open tooltip-error' : ''} data-tip={error}>
                <TextInput type="number"
                           min={1}
                           value={quantity}
                           onChange={(ev) => setQuantity(parseInt(ev.target.value))}
                           onBlur={handleQuantityChange}
                           className="input-sm w-16"></TextInput>
              </div>
            </div>
            <button onClick={() => onDeleteClick()} className="btn btn-sm btn-ghost order-3">
              Delete
            </button>
            <button className="btn btn-sm btn-ghost order-4 whitespace-nowrap">Save for Later</button>
            <div className="font-bold text-lg text-right order-2 sm:order-4 sm:ml-auto">
              <CurrencyFormatter amount={item.price * quantity}/>
            </div>
          </div>
        </div>
      </div>
      <div className="divider"></div>
    </>
  );
}

export default CartItem;
