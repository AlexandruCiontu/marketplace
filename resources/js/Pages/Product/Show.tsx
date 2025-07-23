import {PageProps, Product, VariationTypeOption} from "@/types";
import {Head, Link, router, useForm, usePage} from "@inertiajs/react";
import {useEffect, useMemo, useState} from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import Carousel from "@/Components/Core/Carousel";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import {arraysAreEqual} from "@/helpers";
import StarRating from "@/Components/Core/StarRating";
import Modal from "@/Components/Core/Modal";
import TextAreaInput from "@/Components/Core/TextAreaInput";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import InputError from "@/Components/Core/InputError";

function Show({
                appName, product, variationOptions, hasPurchased
}: PageProps<{
  product: Product,
  variationOptions: number[],
  hasPurchased: boolean
}>) {

  const form = useForm<{
    option_ids: Record<string, number>;
    quantity: number;
    price: number | null;
  }>({
    option_ids: {},
    quantity: 1,
    price: null // TODO populate price on change
  })

  const {url} = usePage();

  const reviewForm = useForm<{
    rating: number;
    comment: string;
  }>({
    rating: 5,
    comment: ''
  });

  const [showReviews, setShowReviews] = useState(false);

  const [selectedOptions, setSelectedOptions] =
    useState<Record<number, VariationTypeOption>>([]);

  const images = useMemo(() => {
    for (let typeId in selectedOptions) {
      const option = selectedOptions[typeId];
      if (option.images.length > 0) return option.images;
    }
    return product.images;
  }, [product, selectedOptions]);

  const computedProduct = useMemo(() => {
    const selectedOptionIds = Object.values(selectedOptions)
      .map(op => op.id)
      .sort();

    for (let variation of product.variations) {
      const optionIds = variation.variation_type_option_ids.sort();
      if (arraysAreEqual(selectedOptionIds, optionIds)) {
        return {
          price: variation.gross_price ?? variation.price,
          quantity: variation.quantity === null ? Number.MAX_VALUE : variation.quantity,
        }
      }
    }
    return {
      price: product.gross_price,
      quantity: product.quantity === null ? Number.MAX_VALUE : product.quantity,
    };
  }, [product, selectedOptions]);


  useEffect(() => {
    for (let type of product.variationTypes) {
      // console.log(variationOptions)
      const selectedOptionId: number = variationOptions[type.id];
      console.log(selectedOptionId, type.options)
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

  const submitReview = (e: React.FormEvent) => {
    e.preventDefault()
    reviewForm.post(route('reviews.store', product.id), {
      preserveScroll: true,
      onSuccess: () => reviewForm.reset()
    })
  }

  const renderProductVariationTypes = () => {
    return (
      product.variationTypes.map((type, i) => (
        <div key={type.id}>
          <b>{type.name}</b>
          {type.type === 'Image' &&
            <div className="flex gap-2 mb-4">
              {type.options.map(option => (
                <div onClick={() => chooseOption(type.id, option)} key={option.id}>
                  {option.images &&
                    <img src={option.images[0].thumb} alt="" className={'w-[64px] h-[64px] object-contain ' + (
                    selectedOptions[type.id]?.id === option.id ?
                      'outline outline-4 outline-primary' : ''
                  )}/>}
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
      ))
    )
  }

  const renderAddToCartButton = () => {
    return (<div className="mb-8 flex gap-4">
        <select value={form.data.quantity}
                onChange={onQuantityChange}
                className="select select-bordered w-full">
          {Array.from({
            length: Math.min(10, computedProduct.quantity)
          }).map((el, i) => (
            <option value={i + 1} key={i + 1}>Quantity: {i + 1}</option>
          ))}
        </select>
        <button onClick={addToCart} className="btn btn-primary">Add to Cart</button>
      </div>
    )
  }

  useEffect(() => {
    const idsMap = Object.fromEntries(
      Object.entries(selectedOptions).map(([typeId, option]: [string, VariationTypeOption]) => [typeId, option.id])
    )
    console.log(idsMap)
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
        <div className="grid gap-4 sm:gap-8 grid-cols-1 lg:grid-cols-12">
          <div className="col-span-12 md:col-span-7">
            <Carousel images={images}/>
          </div>
          <div className="col-span-12 md:col-span-5">
            <h1 className="text-2xl ">{product.title}</h1>

            <p className={'mb-8'}>
              by <Link href={route('vendor.profile', product.user.store_name)} className="hover:underline">
              {product.user.name}
            </Link>&nbsp;
              in <Link href={route('product.byDepartment', product.department.slug)} className="hover:underline">{product.department.name}</Link>
            </p>

            <div>
            <div className="text-3xl font-semibold">
              <CurrencyFormatter amount={computedProduct.price}/>
            </div>
            {product.average_rating !== null && (
              <button onClick={() => setShowReviews(true)} className="mt-2 flex items-center">
                <StarRating rating={product.average_rating} />
                <span className="ml-2 text-sm">{product.average_rating.toFixed(1)} out of 5</span>
              </button>
            )}
          </div>

            {/*<pre>{JSON.stringify(product.variationTypes, undefined, 2)}</pre>*/}
            {renderProductVariationTypes()}

            {computedProduct.quantity != undefined && computedProduct.quantity < 10 &&
              <div className="text-error my-4">
                <span>Only {computedProduct.quantity} left</span>
              </div>
            }
            {renderAddToCartButton()}

            {product.weight &&
              <div className="mb-2">Weight: {product.weight} kg</div>
            }
            {(product.length || product.width || product.height) &&
              <div className="mb-4">Dimensions: {product.length} x {product.width} x {product.height} cm</div>
            }

            <b className="text-xl">About the Item</b>
            <div
              className="wysiwyg-output overflow-x-hidden break-words"
              dangerouslySetInnerHTML={{ __html: product.description }}
            />
          </div>
        </div>
      </div>
      <Modal show={showReviews} onClose={() => setShowReviews(false)}>
        <div className="p-4">
          <h3 className="text-xl mb-4">Reviews</h3>
          {product.reviews.map(r => (
            <div key={r.id} className="mb-4 border-b pb-2">
              <StarRating rating={r.rating} />
              <p className="text-sm text-gray-500">By {r.user.name}</p>
              <p>{r.comment}</p>
            </div>
          ))}
          {hasPurchased && (
            <form onSubmit={submitReview} className="space-y-2">
              <select
                value={reviewForm.data.rating}
                onChange={e => reviewForm.setData('rating', parseInt(e.target.value))}
                className="select select-bordered w-full"
              >
                {[1,2,3,4,5].map(star => (
                  <option key={star} value={star}>{star} stars</option>
                ))}
              </select>
              <TextAreaInput
                value={reviewForm.data.comment}
                onChange={e => reviewForm.setData('comment', e.target.value)}
                className="w-full" placeholder="Write your comment..."/>
              <InputError message={reviewForm.errors.comment} />
              <PrimaryButton type="submit">Submit Review</PrimaryButton>
            </form>
          )}
        </div>
      </Modal>
    </AuthenticatedLayout>
  );
}

export default Show;
