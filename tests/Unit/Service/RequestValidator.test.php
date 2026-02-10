<?php

use React\React\Service\RequestValidator;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;

beforeEach(function () {
    $this->sessionManager = Mockery::mock(SessionManagerInterface::class);
    $this->validator = new RequestValidator($this->sessionManager);
    $this->request = Mockery::mock(RequestInterface::class);
});

afterEach(function () {
    Mockery::close();
});

test('getValidatedBoolParam returns true for string true', function () {
    $this->request->shouldReceive('getParam')
        ->with('test_param')
        ->andReturn('true');
    
    expect($this->validator->getValidatedBoolParam($this->request, 'test_param'))->toBeTrue();
});

test('getValidatedBoolParam returns true for integer 1', function () {
    $this->request->shouldReceive('getParam')
        ->with('test_param')
        ->andReturn(1);
    
    expect($this->validator->getValidatedBoolParam($this->request, 'test_param'))->toBeTrue();
});

test('getValidatedBoolParam returns false for string false', function () {
    $this->request->shouldReceive('getParam')
        ->with('test_param')
        ->andReturn('false');
    
    expect($this->validator->getValidatedBoolParam($this->request, 'test_param'))->toBeFalse();
});

test('getValidatedBoolParam returns false for integer 0', function () {
    $this->request->shouldReceive('getParam')
        ->with('test_param')
        ->andReturn(0);
    
    expect($this->validator->getValidatedBoolParam($this->request, 'test_param'))->toBeFalse();
});

test('getValidatedBoolParam returns default when param is null', function () {
    $this->request->shouldReceive('getParam')
        ->with('test_param')
        ->andReturn(null);
    
    expect($this->validator->getValidatedBoolParam($this->request, 'test_param', true))->toBeTrue();
    expect($this->validator->getValidatedBoolParam($this->request, 'test_param', false))->toBeFalse();
});

test('getValidatedBoolParam returns default for invalid values', function () {
    $this->request->shouldReceive('getParam')
        ->with('test_param')
        ->andReturn('invalid');
    
    expect($this->validator->getValidatedBoolParam($this->request, 'test_param', true))->toBeTrue();
});

test('getValidatedBoolParam handles boolean true', function () {
    $this->request->shouldReceive('getParam')
        ->with('test_param')
        ->andReturn(true);
    
    expect($this->validator->getValidatedBoolParam($this->request, 'test_param'))->toBeTrue();
});

test('getValidatedBoolParam handles boolean false', function () {
    $this->request->shouldReceive('getParam')
        ->with('test_param')
        ->andReturn(false);
    
    expect($this->validator->getValidatedBoolParam($this->request, 'test_param'))->toBeFalse();
});

test('isParameterOverrideAllowed returns true by default', function () {
    expect($this->validator->isParameterOverrideAllowed($this->request))->toBeTrue();
});
