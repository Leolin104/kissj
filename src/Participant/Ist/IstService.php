<?php

namespace kissj\Participant\Ist;

use kissj\User\User;

class IstService {
	/** @var IstRepository */
	private $istRepository;
	
	public function __construct(IstRepository $istRepository) {
		$this->patrolParticipantRepository = $istRepository;
		$this->istRepository = $istRepository;
	}
	
	public function getIst(User $user): Ist {
		if ($this->istRepository->countBy(['user' => $user]) === 0) {
			$ist = new Ist();
			$ist->user = $user;
			$ist->finished = false;
			$this->istRepository->persist($ist);
			return $ist;
		}
		
		$ist = $this->istRepository->findOneBy(['user' => $user]);
		return $ist;
	}
	
	public function isIstDetailsValid(?string $firstName,
									  ?string $lastName,
									  ?string $allergies,
									  ?string $birthDate,
									  ?string $birthPlace,
									  ?string $country,
									  ?string $gender,
									  ?string $permanentResidence,
									  ?string $scoutUnit,
									  ?string $telephoneNumber,
									  ?string $email,
									  ?string $foodPreferences,
									  ?string $cardPassportNumber,
									  ?string $notes,
	
									  ?string $workPreferences,
									  ?string $skills,
									  ?string $languages,
									  ?string $arrivalDate,
									  ?string $leavingDate,
									  ?string $carRegistrationPlate
	): bool {
		$validFlag = true;
		
		foreach ([$birthDate, $arrivalDate, $leavingDate] as $date) {
			if (!empty($date) && $date !== date('Y-m-d', strtotime($date))) {
				$validFlag = false;
				break;
			}
		}
		// check for numbers and plus sight up front only
		if ((!empty ($telephoneNumber)) && preg_match('/^\+?\d+$/', $telephoneNumber) === 0) {
			$validFlag = false;
		}
		if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			$validFlag = false;
		}
		
		return $validFlag;
	}
	
	public function editIstInfo(Ist $ist,
								?string $firstName,
								?string $lastName,
								?string $allergies,
								?string $birthDate,
								?string $birthPlace,
								?string $country,
								?string $gender,
								?string $permanentResidence,
								?string $scoutUnit,
								?string $telephoneNumber,
								?string $email,
								?string $foodPreferences,
								?string $cardPassportNumber,
								?string $notes,
	
								?string $workPreferences,
								?string $skills,
								?string $languages,
								?string $arrivalDate,
								?string $leavingDate,
								?string $carRegistrationPlate) {
		$ist->firstName = $firstName;
		$ist->lastName = $lastName;
		$ist->allergies = $allergies;
		$ist->birthDate = new \DateTime($birthDate);
		$ist->birthPlace = $birthPlace;
		$ist->country = $country;
		$ist->gender = $gender;
		$ist->permanentResidence = $permanentResidence;
		$ist->scoutUnit = $scoutUnit;
		$ist->telephoneNumber = $telephoneNumber;
		$ist->email = $email;
		$ist->foodPreferences = $foodPreferences;
		$ist->cardPassportNumber = $cardPassportNumber;
		$ist->notes = $notes;
		
		$ist->workPreferences = $workPreferences;
		$ist->skills = $skills;
		$ist->languages = $languages;
		$ist->arrivalDate = new \DateTime($arrivalDate);
		$ist->leavingDate = new \DateTime($leavingDate);
		$ist->carRegistrationPlate = $carRegistrationPlate;
		
		$this->istRepository->persist($ist);
	}
	
	public function closeRegistration(Ist $ist) {
		$ist->finished = true;
		$this->istRepository->persist($ist);
	}
}