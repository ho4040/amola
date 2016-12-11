app.controller('#ID#',function($scope, $uibModalInstance, $http, $filter, $timeout, CONFIG, MESSAGE){

	$scope.reload = function(){
		var param = {};
		$http.post(CONFIG.API_BASE_URL+"", param).success(function(resp){

		})
	}

	$scope.cancel = function(){
		$uibModalInstance.dismiss();
	}

	$scope.submit = function(){
		$uibModalInstance.close();
	}

	$scope.reload();

})