<?php

declare(strict_types=1);

namespace App\Serializer\Request;

use App\Contract\Request\Interface\RequestQueryInterface;
use App\Contract\Request\Interface\ValidatableRequestInterface;
use App\Exception\Http\BadRequestException;
use App\Exception\Http\InternalServerErrorException;
use App\Exception\Http\UnprocessableEntityException;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestQueryResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly ArrayTransformerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $argumentType = $argument->getType();

        if (null === $argumentType || !class_exists($argumentType)) {
            return false;
        }

        $reflection = new \ReflectionClass($argumentType);

        return $reflection->implementsInterface(ValidatableRequestInterface::class)
            && $reflection->implementsInterface(RequestQueryInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        try {
            $query = $request->query->all();
            $argumentType = $argument->getType();

            if (null === $argumentType || !class_exists($argumentType)) {
                return [];
            }

            $data = $this->serializer->fromArray($query, $argumentType);
        } catch (\RuntimeException $exception) {
            throw new BadRequestException();
        } catch (\Throwable $exception) {
            throw new InternalServerErrorException();
        }

        $v = $this->validator->validate($data);
        if (count($v) > 0) {
            $exception = new UnprocessableEntityException();
            $validationErrors = [];
            /** @var ConstraintViolation $failedValidation */
            foreach ($v as $failedValidation) {
                $field = $failedValidation->getPropertyPath();
                if (!isset($validationErrors[$field])) {
                    $validationErrors[$field] = [];
                }
                $validationErrors[$field][] = $failedValidation->getMessage();
            }
            $exception->addContext(['errors' => $validationErrors]);

            throw $exception;
        }

        return [$data];
    }
}
