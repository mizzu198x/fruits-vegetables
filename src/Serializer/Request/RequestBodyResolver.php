<?php

declare(strict_types=1);

namespace App\Serializer\Request;

use App\Contract\Request\Interface\RequestBodyInterface;
use App\Contract\Request\Interface\ValidatableRequestInterface;
use App\Exception\Http\BadRequestException;
use App\Exception\Http\InternalServerErrorException;
use App\Exception\Http\UnprocessableEntityException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestBodyResolver implements ValueResolverInterface
{
    public const string NOT_BLANK_VALIDATE_MESSAGE = 'This value should not be blank.';

    public const string MISSING_MANDATORY_FIELD_VALIDATE_MESSAGE = 'Missing mandatory field';

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        try {
            $json = $request->getContent();
            $argumentType = $argument->getType();

            if (null === $argumentType || !class_exists($argumentType)) {
                return [];
            }

            $reflection = new \ReflectionClass($argumentType);

            if (!($reflection->implementsInterface(ValidatableRequestInterface::class)
                && $reflection->implementsInterface(RequestBodyInterface::class))) {
                return [];
            }

            /**
             * @var iterable $data
             */
            $data = $this->serializer->deserialize($json, $argumentType, 'json');
        } catch (\TypeError $exception) {
            throw new UnprocessableEntityException($exception->getMessage());
        } catch (\RuntimeException $exception) {
            throw new BadRequestException($exception->getMessage());
        } catch (\Throwable $exception) {
            throw new InternalServerErrorException($exception->getMessage());
        }

        $v = $this->validator->validate($data);
        /** @var \stdClass|ValidatableRequestInterface $sub */
        foreach ($data as $sub) {
            if ($sub instanceof ValidatableRequestInterface) {
                $v->addAll($this->validator->validate($sub));
            }
        }

        if (count($v) > 0) {
            $isMissingMandatoryField = false;
            $validationErrors = [];
            /** @var ConstraintViolation $failedValidation */
            foreach ($v as $failedValidation) {
                $field = $failedValidation->getPropertyPath();
                if (!isset($validationErrors[$field])) {
                    $validationErrors[$field] = [];
                }

                if ($this->isMissingMandatoryValidate((string) $failedValidation->getMessage())) {
                    $isMissingMandatoryField = true;
                }

                $validationErrors[$field][] = $failedValidation->getMessage();
            }

            $exception = new BadRequestException(
                $isMissingMandatoryField ? self::MISSING_MANDATORY_FIELD_VALIDATE_MESSAGE : '',
            );

            $exception->addContext(['errors' => $validationErrors]);

            throw $exception;
        }

        return [$data];
    }

    private function isMissingMandatoryValidate(string $message): bool
    {
        return self::NOT_BLANK_VALIDATE_MESSAGE === $message;
    }
}
