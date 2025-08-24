import {PageProps, Product, VariationTypeOption} from "@/types";
import {Head, useForm} from "@inertiajs/react";
import {useEffect, useState} from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import {arraysAreEqual} from "@/helpers";

const setQueryParam = (key: string, value: string | number) => {
  const url = new URL(window.location.href);
  url.searchParams.set(key, String(value));
  window.history.replaceState({}, '', url.toString());
};

const getQueryObject = (): Record<string, string> => {
  const params = new URLSearchParams(window.location.search);
  const obj: Record<string, string> = {};
  params.forEach((v, k) => (obj[k] = v));
  return obj;
};

const fetchProduct = async (productIdOrSlug: string) => {
  const qs = window.location.search;
  const res = await fetch(`/api/products/${productIdOrSlug}${qs}`, {
    headers: { 'Accept': 'application/json' },
  });
  const json = await res.json();
  return json.data;
};

function Show({ appName, product: initialProductFromServer }: PageProps<{ product: Product }>) {
  const [product, setProduct] = useState<any>(initialProductFromServer);
  const productIdOrSlug = product.slug ?? product.id;

  const form = useForm<{
    option_ids: Record<string, number>;
    quantity: number;
    price: number | null;
  }>({
    option_ids: {},
    quantity: 1,
    price: null
  })

  const [selectedOptions, setSelectedOptions] =
    useState<Record<number, VariationTypeOption>>([]);


  const [computedProduct, setComputedProduct] = useState<{ price: number; price_gross: number; vat_amount: number; vat_rate: number; vat_type: string; quantity: number }>({
    price: product.price,
    price_gross: product.price_gross,
    vat_amount: product.vat_amount,
    vat_rate: product.vat_rate,
    vat_type: product.vat_type,
    quantity: product.quantity ?? Number.MAX_VALUE,
  });

  useEffect(() => {
    fetchProduct(productIdOrSlug).then(setProduct);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    const selectedOptionIds = Object.values(selectedOptions)
      .map(op => op.id)
      .sort();

    let price = product.price;
    let quantity = product.quantity === null ? Number.MAX_VALUE : product.quantity;

    for (let variation of product.variations) {
      const optionIds = [...variation.variation_type_option_ids].sort();
      if (arraysAreEqual(selectedOptionIds, optionIds)) {
        price = variation.price;
        quantity = variation.quantity === null ? Number.MAX_VALUE : variation.quantity;
        break;
      }
    }

    fetch(`/api/products/${product.id}/price?price=${price}`)
      .then(res => res.json())
      .then(data => setComputedProduct({
        price,
        price_gross: data.price_gross,
        vat_amount: data.vat_amount,
        vat_rate: data.vat_rate,
        vat_type: product.vat_type,
        quantity,
      }));
  }, [selectedOptions, product]);

  const chooseOption = (type: any, option: VariationTypeOption) => {
    setSelectedOptions(prev => ({ ...prev, [type.id]: option }));
    const typeKey = (type.key ?? type.name.toLowerCase().replace(/\s+/g, '_'));
    setQueryParam(typeKey, option.id);
    fetchProduct(productIdOrSlug).then(setProduct);
  };

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
    return product.variationTypes.map((type: any, i: number) => (
      <div key={type.id}>
        <b>{type.name}</b>
        {type.type === 'Image' &&
          <div className="flex gap-2 mb-4">
            {type.options.map((option: VariationTypeOption) => (
              <div onClick={() => chooseOption(type, option)} key={option.id}>
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
            {type.options.map((option: VariationTypeOption) => (
              <input onChange={() => chooseOption(type, option)}
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
        <meta property="og:image" content={product.images?.[0]}/>
        <meta property="og:url" content={route('product.show', product.slug)}/>
        <meta property="og:type" content="product"/>
        <meta property="og:site_name" content={appName}/>
      </Head>

      <div className="container mx-auto p-8">
        <div className="space-y-12">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div className="grid grid-cols-1 gap-2">
              {Array.isArray(product.images) && product.images.map((url: string, idx: number) => (
                <img key={idx} src={url} alt={product.title} className="rounded-xl" />
              ))}
            </div>
            <div>
              <h1 className="text-2xl font-bold">{product.title}</h1>
              <p className="text-gray-600">
                {product.user.name} in {product.department.name}
              </p>
              <p className="text-3xl font-semibold mt-4">
                <CurrencyFormatter amount={computedProduct.price_gross ?? 0} />
              </p>
              {computedProduct.vat_amount && computedProduct.vat_amount > 0 && (
                <p className="text-sm text-gray-500">
                  Includes VAT: <CurrencyFormatter amount={computedProduct.vat_amount ?? 0} /> ({computedProduct.vat_rate ?? 0}%)
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

