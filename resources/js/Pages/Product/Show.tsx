import {PageProps, Product, VariationTypeOption, Image} from "@/types";
import {Head, router, useForm, usePage} from "@inertiajs/react";
import {useEffect, useMemo, useState} from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import ProductGallery from "@/Components/Core/Carousel";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import {arraysAreEqual} from "@/helpers";
import { getVatRate, calculateVatIncludedPrice, calculateVatAmount } from '@/utils/vat';

function Show({
                appName, product, variationOptions
              }: PageProps<{
  product: Product,
  variationOptions: number[]
}>) {
  const form = useForm<{
    option_ids: Record<string, number>;
    quantity: number;
    price: number | null;
  }>({
    option_ids: {},
    quantity: 1,
    price: null
  })

  const {url} = usePage();
  const [selectedOptions, setSelectedOptions] =
    useState<Record<number, VariationTypeOption>>([]);

  const images = useMemo<Image[]>(() => {
    for (let typeId in selectedOptions) {
      const option = selectedOptions[typeId];
      if (option.images.length > 0) return [...option.images];
    }
    return [...product.images];
  }, [product, selectedOptions]);

  const { countryCode } = usePage<PageProps>().props as PageProps;

  const computedProduct = useMemo(() => {
    const selectedOptionIds = Object.values(selectedOptions)
      .map(op => op.id)
      .sort();

    let price = product.price;
    let quantity = product.quantity === null ? Number.MAX_VALUE : product.quantity;

    for (let variation of product.variations) {
      const optionIds = variation.variation_type_option_ids.sort();
      if (arraysAreEqual(selectedOptionIds, optionIds)) {
        price = variation.price;
        quantity = variation.quantity === null ? Number.MAX_VALUE : variation.quantity;
        break;
      }
    }

    const rate = getVatRate(countryCode, (product.vat_rate_type as any) ?? 'standard_rate');
    const gross_price =
      price === product.price && product.gross_price !== undefined
        ? product.gross_price
        : calculateVatIncludedPrice(price, rate);
    const vat_amount =
      price === product.price && product.gross_price !== undefined
        ? product.gross_price - price
        : calculateVatAmount(price, rate);
    return {
      price,
      gross_price,
      vat_amount,
      vat_rate_type: product.vat_rate_type,
      quantity,
    };
  }, [product, selectedOptions, countryCode]);

  useEffect(() => {
    for (let type of product.variationTypes) {
      const selectedOptionId: number = variationOptions[type.id];
      chooseOption(
        type.id,
        type.options.find(op => op.id == selectedOptionId) || type.options[0],
        false
      )
    }
  }, []);

  const getOptionIdsMap = (newOptions: object) => {
    return Object.fromEntries(
      Object.entries(newOptions).map(([a, b]) => [a, b.id])
    )
  }

  const chooseOption = (
    typeId: number,
    option: VariationTypeOption,
    updateRouter: boolean = true
  ) => {
    setSelectedOptions((prevSelectedOptions) => {
      const newOptions = {
        ...prevSelectedOptions,
        [typeId]: option
      }

      if (updateRouter) {
        router.get(url, {
          options: getOptionIdsMap(newOptions)
        }, {
          preserveScroll: true,
          preserveState: true
        })
      }

      return newOptions
    })
  }

  const onQuantityChange = (ev: React.ChangeEvent<HTMLSelectElement>) => {
    form.setData('quantity', parseInt(ev.target.value))
  }

  const addToCart = () => {
    form.post(route('cart.store', product.id), {
      preserveScroll: true,
      preserveState: true,
      onError: (err) => {
        console.log(err)
      }
    })
  }

  const renderProductVariationTypes = () => {
    return product.variationTypes.map((type, i) => (
      <div key={type.id}>
        <b>{type.name}</b>
        {type.type === 'Image' &&
          <div className="flex gap-2 mb-4">
            {type.options.map(option => (
              <div onClick={() => chooseOption(type.id, option)} key={option.id}>
                {option.images.length > 0 &&
                  <img src={option.images[0].thumb} alt="" className={'w-[64px] h-[64px] object-contain ' + (
                    selectedOptions[type.id]?.id === option.id ? 'outline outline-4 outline-primary' : ''
                  )}/>
                }
              </div>
            ))}
          </div>}
        {type.type === 'Radio' &&
          <div className="flex join mb-4">
            {type.options.map(option => (
              <input onChange={() => chooseOption(type.id, option)}
                     key={option.id}
                     className="join-item btn"
                     type="radio"
                     value={option.id}
                     checked={selectedOptions[type.id]?.id === option.id}
                     name={'variation_type_' + type.id}
                     aria-label={option.name}/>
            ))}
          </div>}
      </div>
    ));
  }

  const renderAddToCartButton = () => {
    return (
      <div className="mb-8 flex gap-4">
        <select value={form.data.quantity}
                onChange={onQuantityChange}
                className="select select-bordered w-full">
          {Array.from({length: Math.min(10, computedProduct.quantity)}).map((_, i) => (
            <option value={i + 1} key={i + 1}>Quantity: {i + 1}</option>
          ))}
        </select>
        <button onClick={addToCart} className="btn btn-primary">Add to Cart</button>
      </div>
    );
  }

  const ProductDetails = () => (
    <div>
      {renderProductVariationTypes()}

      {computedProduct.quantity != undefined && computedProduct.quantity < 10 && (
        <div className="text-error my-4">
          <span>Only {computedProduct.quantity} left</span>
        </div>
      )}

      {renderAddToCartButton()}

      {product.weight && (
        <div className="mb-8">
          <h2 className="font-semibold text-gray-700 text-lg">Product Details</h2>
          <ul className="text-gray-600 list-disc list-inside space-y-1">
            <li>Weight: {product.weight} kg</li>
            <li>Length: {product.length} cm</li>
            <li>Width: {product.width} cm</li>
            <li>Height: {product.height} cm</li>
          </ul>
        </div>
      )}
    </div>
  );

  useEffect(() => {
    const idsMap = Object.fromEntries(
      Object.entries(selectedOptions).map(([typeId, option]: [string, VariationTypeOption]) => [typeId, option.id])
    )
    form.setData('option_ids', idsMap)
  }, [selectedOptions]);

  return (
    <AuthenticatedLayout>
      <Head>
        <title>{product.title}</title>
        <meta name="title" content={product.meta_title || product.title}/>
        <meta name="description" content={product.meta_description}/>
        <link rel="canonical" href={route('product.show', product.slug)}/>
        <meta property="og:title" content={product.title}/>
        <meta property="og:description" content={product.meta_description}/>
        <meta property="og:image" content={images[0]?.small}/>
        <meta property="og:url" content={route('product.show', product.slug)}/>
        <meta property="og:type" content="product"/>
        <meta property="og:site_name" content={appName}/>
      </Head>

      <div className="container mx-auto p-8">
        <div className="space-y-12">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <ProductGallery images={images} />
            <div>
              <h1 className="text-2xl font-bold">{product.title}</h1>
              <p className="text-gray-600">
                {product.user.name} in {product.department.name}
              </p>
              <p className="text-3xl font-semibold mt-4">
                <CurrencyFormatter amount={computedProduct.gross_price ?? 0} />
              </p>
              {computedProduct.vat_amount && computedProduct.vat_amount > 0 && (
                <p className="text-sm text-gray-500">
                  Includes VAT: <CurrencyFormatter amount={computedProduct.vat_amount ?? 0} />
                </p>
              )}
              <ProductDetails />
            </div>
          </div>

          <div className="prose max-w-none">
            <h2>About the Item</h2>
            <div dangerouslySetInnerHTML={{ __html: product.description }} />
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

export default Show;

