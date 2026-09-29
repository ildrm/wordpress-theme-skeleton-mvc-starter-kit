<?php

declare(strict_types=1);

namespace WPMVC\Foundation;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use WPMVC\Exceptions\BindingResolutionException;
use WPMVC\Exceptions\CircularDependencyException;

class Container {

	/** @var array<string, array{concrete: string|callable, shared: bool}> */
	private array $bindings = [];

	/** @var array<string, mixed> */
	private array $instances = [];

	/** @var array<string, string> */
	private array $aliases = [];

	/** @var list<string> */
	private array $resolving = [];

	public function bind( string $abstract, string|callable|null $concrete = null ): void {
		$this->storeBinding( $abstract, $concrete ?? $abstract, false );
	}

	public function singleton( string $abstract, string|callable|null $concrete = null ): void {
		$this->storeBinding( $abstract, $concrete ?? $abstract, true );
	}

	public function instance( string $abstract, mixed $instance ): void {
		$abstract = $this->canonical( $abstract );
		$this->ensureCompatible( $abstract, $instance );
		unset( $this->bindings[ $abstract ] );
		$this->instances[ $abstract ] = $instance;
	}

	public function alias( string $abstract, string $alias ): void {
		if ( $abstract === '' || $alias === '' || $abstract === $alias ) {
			throw new BindingResolutionException( 'Container aliases require two distinct, nonempty names.' );
		}

		$this->aliases[ $alias ] = $abstract;

		try {
			$this->canonical( $alias );
		} catch ( CircularDependencyException $exception ) {
			unset( $this->aliases[ $alias ] );
			throw $exception;
		}
	}

	public function bound( string $abstract ): bool {
		$abstract = $this->canonical( $abstract );

		return array_key_exists( $abstract, $this->instances ) || isset( $this->bindings[ $abstract ] );
	}

	/** @param array<string, mixed> $parameters */
	public function make( string $abstract, array $parameters = [] ): mixed {
		$abstract = $this->canonical( $abstract );

		if ( array_key_exists( $abstract, $this->instances ) && $parameters === [] ) {
			return $this->instances[ $abstract ];
		}

		if ( in_array( $abstract, $this->resolving, true ) ) {
			throw new CircularDependencyException(
				sprintf(
					'Circular dependency detected: %s',
					implode( ' -> ', [ ...$this->resolving, $abstract ] )
				)
			);
		}

		$this->resolving[] = $abstract;

		try {
			$binding  = $this->bindings[ $abstract ] ?? null;
			$concrete = $binding['concrete'] ?? $abstract;

			if ( is_string( $concrete ) ) {
				$object = $concrete === $abstract
					? $this->build( $concrete, $parameters )
					: $this->make( $concrete, $parameters );
			} else {
				$object = $this->call(
					$concrete,
					[
						'app'        => $this,
						'container'  => $this,
						'parameters' => $parameters,
					]
				);
			}

			$this->ensureCompatible( $abstract, $object );

			if ( ( $binding['shared'] ?? false ) && $parameters === [] ) {
				$this->instances[ $abstract ] = $object;
			}

			return $object;
		} finally {
			array_pop( $this->resolving );
		}
	}

	/**
	 * Invoke a function, closure, invokable object, or class method with injected dependencies.
	 * Explicit parameters can be keyed by argument name or class/interface name.
	 *
	 * @param callable|array{0: object|string, 1: string}|string $callback
	 * @param array<string, mixed> $parameters
	 */
	public function call( callable|array|string $callback, array $parameters = [] ): mixed {
		if ( is_string( $callback ) && str_contains( $callback, '@' ) ) {
			[$class, $method] = explode( '@', $callback, 2 );
			$callback         = [ $this->make( $class ), $method ];
		} elseif ( is_string( $callback ) && str_contains( $callback, '::' ) ) {
			[$class, $method] = explode( '::', $callback, 2 );
			$reflection       = new ReflectionMethod( $class, $method );
			$callback         = [ $reflection->isStatic() ? $class : $this->make( $class ), $method ];
		} elseif ( is_string( $callback ) && class_exists( $callback ) ) {
			$callback = $this->make( $callback );
		} elseif ( is_array( $callback ) && is_string( $callback[0] ) ) {
			$callback = [ $this->make( $callback[0] ), $callback[1] ];
		}

		if ( ! is_callable( $callback ) ) {
			throw new BindingResolutionException( 'The supplied callback is not callable.' );
		}

		$reflection = is_array( $callback )
			? new ReflectionMethod( $callback[0], $callback[1] )
			: ( is_object( $callback ) && ! $callback instanceof Closure
				? new ReflectionMethod( $callback, '__invoke' )
				: new ReflectionFunction( $callback ) );

		return $callback( ...$this->resolveParameters( $reflection, $parameters ) );
	}

	/** @param array<string, mixed> $parameters */
	private function build( string $class, array $parameters ): object {
		if ( ! class_exists( $class ) ) {
			throw new BindingResolutionException( sprintf( 'Cannot resolve %s: class does not exist or has no binding.', $class ) );
		}

		$reflection = new ReflectionClass( $class );

		if ( ! $reflection->isInstantiable() ) {
			throw new BindingResolutionException( sprintf( 'Cannot instantiate %s.', $class ) );
		}

		$constructor = $reflection->getConstructor();

		if ( $constructor === null ) {
			return $reflection->newInstance();
		}

		return $reflection->newInstanceArgs( $this->resolveParameters( $constructor, $parameters ) );
	}

	/**
	 * @param array<string, mixed> $overrides
	 * @return list<mixed>
	 */
	private function resolveParameters( ReflectionFunctionAbstract $function, array $overrides ): array {
		$resolved = [];

		foreach ( $function->getParameters() as $parameter ) {
			$name = $parameter->getName();
			$type = $parameter->getType();

			if ( array_key_exists( $name, $overrides ) ) {
				$resolved[] = $overrides[ $name ];
				continue;
			}

			if ( $type instanceof ReflectionNamedType && ! $type->isBuiltin() ) {
				$class = $type->getName();

				if ( array_key_exists( $class, $overrides ) ) {
					$resolved[] = $overrides[ $class ];
					continue;
				}

				$resolved[] = $this->make( $class );
				continue;
			}

			if ( $parameter->isDefaultValueAvailable() ) {
				$resolved[] = $parameter->getDefaultValue();
				continue;
			}

			if ( $parameter->allowsNull() ) {
				$resolved[] = null;
				continue;
			}

			throw new BindingResolutionException(
				sprintf(
					'Cannot resolve parameter $%s of %s. Provide an override or a class binding.',
					$name,
					$this->describeParameter( $parameter )
				)
			);
		}

		return $resolved;
	}

	private function describeParameter( ReflectionParameter $parameter ): string {
		$function = $parameter->getDeclaringFunction();

		return $function instanceof ReflectionMethod
			? $function->getDeclaringClass()->getName() . '::' . $function->getName() . '()'
			: $function->getName() . '()';
	}

	private function storeBinding( string $abstract, string|callable $concrete, bool $shared ): void {
		if ( $abstract === '' ) {
			throw new BindingResolutionException( 'Binding name cannot be empty.' );
		}

		$abstract = $this->canonical( $abstract );
		unset( $this->instances[ $abstract ] );
		$this->bindings[ $abstract ] = [
			'concrete' => $concrete,
			'shared'   => $shared,
		];
	}

	private function canonical( string $abstract ): string {
		$seen = [];

		while ( isset( $this->aliases[ $abstract ] ) ) {
			if ( isset( $seen[ $abstract ] ) ) {
				throw new CircularDependencyException( sprintf( 'Circular container alias: %s.', implode( ' -> ', [ ...array_keys( $seen ), $abstract ] ) ) );
			}

			$seen[ $abstract ] = true;
			$abstract          = $this->aliases[ $abstract ];
		}

		return $abstract;
	}

	private function ensureCompatible( string $abstract, mixed $value ): void {
		if ( ( class_exists( $abstract ) || interface_exists( $abstract ) ) && ! ( is_object( $value ) && is_a( $value, $abstract ) ) ) {
			throw new BindingResolutionException( sprintf( 'Binding for %s must resolve to an instance of that type.', $abstract ) );
		}
	}
}
