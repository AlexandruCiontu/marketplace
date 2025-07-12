import {Product, ProductListItem} from "@/types";
import {Link, useForm} from "@inertiajs/react";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";

export default function ProductItem({product}: { product: ProductListItem }) {

  const form = useForm<{
    option_ids: Record<string, number>;
    quantity: number;
  }>({
    option_ids: {},
    quantity: 1,
  })

  const addToCart = () => {
    form.post(route('cart.store', product.id), {
      preserveScroll: true,
      preserveState: true,
      onError: (err) => {
        console.error(err)
      }
    })
  }

  return (
    <div className="card bg-base-100 shadow">
      <Link href={route('product.show', product.slug)}>
        <figure>
          <img
            src={product.image}
            alt={product.title}
            className="w-full h-48 aspect-square object-contain"/>
        </figure>
      </Link>
      <div className="card-body p-6">
        <Link href={route('product.show', product.slug)}>
          <h2 className="card-title text-sm">{(product.title && product.title.length > 50) ? (product.title.substring(0, 90) + '...') : product.title}</h2>
        </Link>
        <p className={"text-sm"}>
          by <Link href={route('vendor.profile', product.user_store_name)} className="hover:underline">
          {product.user_name}
        </Link>&nbsp;
          in <Link href={route('product.byDepartment', product.department_slug)}
                   className="hover:underline">{product.department_name}</Link>
        </p>
        <div className="card-actions items-center justify-between mt-3">
          <button onClick={addToCart} className="btn btn-primary">Add to Cart</button>
          <span className="text-2xl">
            <CurrencyFormatter amount={product.price}/>
          </span>
        </div>
      </div>
    </div>
  )
}
