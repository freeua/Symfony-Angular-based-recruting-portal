import { Component, ElementRef, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
import { CandidateService } from '../../../../services/candidate.service';
import { SharedService } from '../../../../services/shared.service';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Observable} from "rxjs/Observable";
import 'rxjs/add/observable/timer';
import 'rxjs/add/operator/map';
import 'rxjs/add/operator/take';
import { Router } from '@angular/router';
import { AdminCandidateProfile } from '../../../../../entities/models-admin';

// declare let clipchamp: any;

@Component({
  selector: 'app-candidate-video',
  templateUrl: './candidate-video.component.html',
  styleUrls: ['./candidate-video.component.scss']
})
export class CandidateVideoComponent implements OnInit {
  @ViewChild('videoBlock') public videoBlock : ElementRef;
  @ViewChild('videoPlayer') public videoPlayer: ElementRef;
  @ViewChild('content') public content : ElementRef;
  @ViewChild('iframe') public iframe : ElementRef;

  @ViewChild('recVideo') public recVideo: ElementRef;

  @Input() constrains = { video: true, audio: true };
  @Input() fileName = 'my_recording';
  @Input() showVideoPlayer = true;

  @Output() startRecording = new EventEmitter();
  @Output() downloadRecording = new EventEmitter();
  @Output() fetchRecording = new EventEmitter();

  public videoObject: any;

  public preloaderPage = true;
  public buttonPreloader = false;
  public preloaderVideo = false;
  public preloaderNewVideo = false;
  public modalActiveClose: any;
  public buttonVideoPreloader = false;

  public format = 'video/webm';
  public _navigator = <any> navigator;
  public localStream;
  public video;
  public mediaRecorder;
  public recordedBlobs = null;
  public hideStopBtn = true;
  public videoRecordPopup = false;
  public videoUploadPopup = false;
  public videoRecordStatus = false;

  public countDown;
  public counter = 300;
  public tick = 1000;
  public showCurrentTime = false;
  public currentTime;
  public previewRecordInfo = true;
  public mobileButton = false;

  public candidateProfileDetails: AdminCandidateProfile;
  public visibilityLooking = false;
  public visibilityVisible = false;
  public checkLooking: boolean;
  public checkVisible: boolean;
  public videoUploadPopups = false;
  public visibleActivePopup = false;

  public checkVideo;
  public allowVideo;

  constructor(
    public readonly _candidateService: CandidateService,
    public readonly _sharedService: SharedService,
    public readonly _toastr: ToastrService,
    public readonly _modalService: NgbModal,
    private readonly _router: Router
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    /*const el = document.querySelector("#clipchamp-button");
      // For more customization options refer to our Documentation.
    const options = {
      output: "blob",
      title: "Record new video",
      style: {
        url:   "./video.css",
      },
      onVideoCreated: function (data) {
        console.log(data);
      }
    };*/
    // clipchamp(el, options);
    window.scrollTo(0, 0);

    if (this.recVideo) {
      this.video = this.recVideo.nativeElement;
    }

    if(this._navigator.getUserMedia !== undefined) {
      this._navigator.getUserMedia = ( this._navigator.mediaDevices.getUserMedia || this._navigator.getUserMedia || this._navigator.webkitGetUserMedia
      || this._navigator.mozGetUserMedia || this._navigator.msGetUserMedia );
    }

    this.getCandidateVideo().then(() => {
      this.getCandidateProfile();
    });

    this._sharedService.progressBar = Number(localStorage.getItem('progressBar'));

    this.isMobile();
  }

  /**
   * Change status candidate
   * @param field {string}
   * @param value {boolean}
   */
  public changeStatusCandidate(field: string, value: boolean){
    let error = true;
    if(this.candidateProfileDetails.profile.percentage < 50) {
      this._toastr.error('Your profile needs to be 50% complete');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if(!this.candidateProfileDetails.profile.cvFiles || this.candidateProfileDetails.profile.cvFiles.length === 0){
      this._toastr.error('You need to upload CV file');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if(this.candidateProfileDetails.profile.cvFiles[0] && !this.candidateProfileDetails.profile.cvFiles[0].approved){
      this._toastr.error('Your CV files is not approved by the administrator');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if (!this.candidateProfileDetails.profile.video && this.candidateProfileDetails.allowVideo === false) {
      this._toastr.error('You need to upload video');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if (this.candidateProfileDetails.profile.video && !this.candidateProfileDetails.profile.video.approved && this.candidateProfileDetails.allowVideo === false) {
      this._toastr.error('Your video is not approved by the administrator');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if(field === 'visible'){
      if(this.checkLooking !== true){
        this._toastr.error('You need to turn ON "I am looking for a job"');
        error = false;
        this.visibilityVisible = true;
      }
      else{
        this.visibilityVisible = false;
      }
    }
    if (error === true) {
      if (field === 'looking' && value === false) {
        this.closeLookingPopup(true, false);
      } else if (field === 'visible' && value === true) {
        this.closeVisiblePopup(true, true);
      } else {
        const data = {[field]:value};

        this._candidateService.changeStatusCandidate(data).then(data => {
          this.checkLooking = data.looking;
          this.checkVisible = data.visible;
          if(this.checkLooking === true){
            this.visibilityVisible = false;
          }
          else{
            this.visibilityVisible = true;
          }
          if (field === 'looking') {
            this._toastr.success('Your profile is now active');
          } else if (field === 'visible' && value === true) {
            this._toastr.success('Your profile is now visible to all employers');
          } else {
            this._toastr.success('Your profile is now not visible to all employers');
          }
        }).catch(err => {
          this._sharedService.showRequestErrors(err);
        });
      }
    }
  }


  /**
   * Status looking popup
   * @param value
   * @param check
   */
  public closeLookingPopup(value, check) {
    this.videoUploadPopups = value;
    this.checkLooking = check;
  }

  /**
   * Status visible popup
   * @param value
   * @param check
   */
  public closeVisiblePopup(value, check) {
    this.visibleActivePopup = value;
    if(check === false) {
      this.checkVisible = false;
    } else {
      this.checkVisible = true;
    }
  }

  /**
   * Send request looking job
   * @param field
   * @param value
   */
  public lookingJobToggle(field: string, value: boolean) {
    this.checkLooking = false;
    const data = {[field]:value};

    this._candidateService.changeStatusCandidate(data).then(data => {
      this.checkLooking = data.looking;
      this.checkVisible = data.visible;
      if(this.checkLooking === true){
        this.visibilityVisible = false;
      }
      else{
        this.visibilityVisible = true;
      }
      this._toastr.error('Your profile is now disabled');
    }).catch(err => {
      this._sharedService.showRequestErrors(err);
    });
  }

  /**
   * Send request visible jobs
   * @param field
   * @param value
   */
  public visibleJobToggle(field: string, value: boolean) {
    this.checkVisible = true;
    const data = { [field]: value };

    this._candidateService.changeStatusCandidate(data).then(data => {
      this.checkVisible = data.visible;
      this._toastr.success('Your profile is now visible to all employers');
    }).catch(err => {
      this._sharedService.showRequestErrors(err);
    });
  }

  /**
   * Select change router
   * @param url
   */
  public routerApplicants(url): void {
    this._router.navigate([url]);
  }

  /**
   * Get candidate profile
   * @returns {Promise<void>}
   */
  public async getCandidateProfile(): Promise<any> {
    try {
      const data = await this._candidateService.getCandidateProfileDetails();
      this._sharedService.progressBar = data.profile.percentage;
      this.checkVideo = data.profile.video;
      this.allowVideo = data['allowVideo'];
      this.candidateProfileDetails = data;
      if(this.candidateProfileDetails.profile.percentage < 50 || !this.candidateProfileDetails.profile.cvFiles ||
        !this.candidateProfileDetails.profile.cvFiles[0] ||
        !this.candidateProfileDetails.profile.cvFiles[0].approved ||
        (this.candidateProfileDetails.allowVideo === false && !this.candidateProfileDetails.profile.video) ||
        (this.candidateProfileDetails.allowVideo === false && this.candidateProfileDetails.profile.video && this.candidateProfileDetails.profile.video.approved === false)) {
        this.checkVisible = false;
        this.checkLooking = false;
        this.visibilityLooking = true;
        this.visibilityVisible = true;
      } else {
        this.checkVisible = this.candidateProfileDetails.profile.visible;
        this.checkLooking = this.candidateProfileDetails.profile.looking;
      }
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Step to next page
   */
  public stepNextPage(): void {
    this._router.navigate(['/candidate/preferences']);
  }

  /**
   * Detected Mobile
   * @returns {boolean}
   */
  public isMobile() {
    if( navigator.userAgent.match(/Android/i)
      || navigator.userAgent.match(/webOS/i)
      || navigator.userAgent.match(/iPhone/i)
      || navigator.userAgent.match(/iPad/i)
      || navigator.userAgent.match(/iPod/i)
      || navigator.userAgent.match(/BlackBerry/i)
      || navigator.userAgent.match(/Windows Phone/i)
    ){
      this.mobileButton = true;
    }
    else {
      this.mobileButton = false;
    }
  }

  /**
   * Init Stream
   * @param constrains
   * @param navigator
   * @return {Promise<TResult|TResult2|TResult1>}
   * @private
   */
  private _initStream(constrains, navigator) {
    return navigator.mediaDevices.getUserMedia(constrains)
      .then((stream) => {
        this.localStream = stream;
        if(window.URL && window.URL.createObjectURL){
          try {
            return window.URL.createObjectURL(stream);
          }
          catch (err){
            return stream;
          }
        }
        else{
          return stream;
        }
        //return (window.URL && window.URL.createObjectURL) ? window.URL.createObjectURL(stream) : stream;
      })
      .catch(err => err);
  }

  public pause() {
    this.mediaRecorder.pause();
    this.recVideo.nativeElement.pause();

    this.showCurrentTime = true;
    this.currentTime = this.counter;
  }

  public resume() {
    this.mediaRecorder.resume();
    this.recVideo.nativeElement.play();
    this.showCurrentTime = false;
    this.counter = this.currentTime;
  }

  /**
   * Stop stream
   * @private
   */
  private _stopStream() {
    const tracks = this.localStream.getTracks();
    tracks.forEach((track) => {
      track.stop();
    });
  }

  /**
   * Start record video
   */
  public start() {
    this.previewRecordInfo = false;

    if (this.video) {
      this.video.controls = false;
      this.video.muted = false;
    }

    this._initStream(this.constrains, this._navigator)
      .then((stream) => {
        if (!window['MediaRecorder']) {
          alert('Your browser is not supported record video. We recommend using the latest version of Chrome or Firefox');
          this.openVideoPopup();
          return;
        }
        if (!window['MediaRecorder'].isTypeSupported(this.format)) {
          alert('Your browser is not supported record video. We recommend using the latest version of Chrome or Firefox');
          this.openVideoPopup();
          return;
        }
        try {
          this.mediaRecorder = new window['MediaRecorder'](this.localStream, {mimeType: this.format});
          if (this.video) {
            if(typeof stream === 'object'){
              this.video.srcObject = stream;
            }
            else{
              this.video.src = stream;
            }
          }
          this.startRecording.emit(stream);
          setTimeout(() => {
            this.countDown = Observable.timer(0, this.tick)
              .take(this.counter)
              .map(() => --this.counter );
          }, 1500);
        } catch (e) {
          alert('Devices not found');
          this.openVideoPopup();
          return;
        }
        this.recordedBlobs = [];
        this.hideStopBtn = false;
        this.counter = 300;
        this.mediaRecorder.ondataavailable =
          (event) => {
            if (event.data && event.data.size > 0) {
              this.recordedBlobs.push(event.data);
              if (this.counter === 0) {
                this.hideStopBtn = true;

                this._stopStream();
                //this.mediaRecorder.stop();
                this.fetchRecording.emit(this.recordedBlobs);
                if (this.video) {
                  this.video.controls = true;
                  this.video.muted = true;
                }
              }
            }
        };
        this.mediaRecorder.start(10);

    });
  }

  /**
   * Clear video from popup
   */
  public clearVideo() {
    this.recVideo.nativeElement.src = '';
    this.recVideo.nativeElement.load();
    this.recordedBlobs = [];
    if (this.video) {
      this.video.controls = false;
    }
    this.previewRecordInfo = true;
  }

  /**
   * Stop record video
   */
  public stop() {
    this.showCurrentTime = false;
    if(this.hideStopBtn === false){
      this.hideStopBtn = true;

      this._stopStream();
      this.mediaRecorder.stop();
      this.fetchRecording.emit(this.recordedBlobs);
      if (this.video) {
        this.video.controls = true;
      }
    }
  }

  /**
   * Play recording video
   */
  public play() {
    if (!this.video) {
      return;
    }
    const superBuffer = new Blob(this.recordedBlobs, {type: this.format});
    this.video.pause();
    this.video.src = null;
    this.video.srcObject = null;
    this.video.load();
    this.video.src = window.URL.createObjectURL(superBuffer);
    this.video.load();
    this.video.play();

  }

  /**
   * Download video
   */
  public download() {
    const timestamp = new Date().getUTCMilliseconds();
    const blob = new Blob(this.recordedBlobs, {type: this.format});
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.style.display = 'none';
    a.href = url;
    a.download = timestamp + '__' + this.fileName + '.webm';
    document.body.appendChild(a);
    a.click();
    setTimeout(() => {
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
      this.downloadRecording.emit();
    }, 100);
  }

  /**
   * Upload recording video
   */
  public uploadBlobVideo(): void{
    this.videoRecordPopup = false;
    this.videoRecordStatus = true;
    this.preloaderVideo = true;
    this.preloaderNewVideo = true;

    const superBuffer = new Blob(this.recordedBlobs, {type: this.format});
    const formData = new FormData();
    formData.append('video', superBuffer, 'test.webm');
    try {
      this._candidateService.uploadBlobVideo(formData).then(data => {
        setTimeout(() => {
          this.videoObject = data.video;
          if (this.videoPlayer) {
            this.videoPlayer.nativeElement.pause();
            this.videoPlayer.nativeElement.src = null;
            this.videoPlayer.nativeElement.load();
            this.videoPlayer.nativeElement.src = this.videoObject.url;
            this.videoPlayer.nativeElement.load();
            this.videoPlayer.nativeElement.play();
          }

          this.checkVisible = data.visible;
          this.checkLooking = data.looking;

          this.videoRecordStatus = false;
          this.preloaderVideo = false;
          this.preloaderNewVideo = false;

          localStorage.setItem('progressBar', data.percentage);
          this._sharedService.progressBar = Number(localStorage.getItem('progressBar'));
          this._sharedService.visibleErrorVideoIcon = false;
          this._toastr.success('Video has been uploaded');
        }, 1000);
      });
    }
    catch (err) {
      this.videoRecordStatus = false;
      this.preloaderVideo = false;
      this.preloaderNewVideo = false;
      this._sharedService.showRequestErrors(err);
    }
  }

  public openVideoPopup(): void {
    this.videoRecordPopup = !this.videoRecordPopup;
  }

  public openUploadPopup(): void {
    this.videoUploadPopup = !this.videoUploadPopup;
  }

  /**
   * Upload candidate video
   * @return {Promise<void>}
   */
  public async uploadVideo(): Promise<void> {
    this.buttonPreloader = true;
    this.preloaderVideo = true;
    this.preloaderNewVideo = true;

    const formData = new FormData();

    for (let i = 0; i < this.videoBlock.nativeElement.files.length; i++) {
      formData.append('video', this.videoBlock.nativeElement.files[i]);
    }

    try {
      this._candidateService.uploadVideo(formData).then(data => {
      setTimeout(() => {
        this.videoObject = data.video;
        if (this.videoPlayer) {
          this.videoPlayer.nativeElement.pause();
          this.videoPlayer.nativeElement.src = null;
          this.videoPlayer.nativeElement.load();
          this.videoPlayer.nativeElement.src = this.videoObject.url;
          this.videoPlayer.nativeElement.load();
          this.videoPlayer.nativeElement.play();
        }

        this.checkVisible = data.visible;
        this.checkLooking = data.looking;

        this.buttonPreloader = false;
        this.preloaderVideo = false;
        this.preloaderNewVideo = false;

        localStorage.setItem('progressBar', data.percentage);
        this._sharedService.progressBar = Number(localStorage.getItem('progressBar'));
        this._sharedService.visibleErrorVideoIcon = false;
        this._toastr.success('Video has been uploaded');
      }, 1000);
      });
    } catch(err) {
      this._sharedService.showRequestErrors(err);
      this.buttonPreloader = false;
      this.preloaderVideo = false;
      this.preloaderNewVideo = false;
    }
  }

  /**
   * Remove candidate video
   * @return {Promise<void>}
   */
  public async removeVideo(): Promise<void> {
    this.preloaderVideo = true;
    this.modalActiveClose.dismiss();
    try {
      const response = await this._candidateService.removeVideo();

      this.videoObject = null;

      localStorage.setItem('progressBar', response.percentage);
      this._sharedService.progressBar = Number(localStorage.getItem('progressBar'));
      this.preloaderVideo = false;
      this._toastr.success('Video has been removed');
      this._sharedService.visibleErrorVideoIcon = true;
      this.candidateProfileDetails.profile.video = null;
      this.checkVisible = response.visible;
      this.checkLooking = response.looking;
      // if (this.allowVideo === false) {
      //
      // } else {
      //   this.candidateProfileDetails.profile.video = null;
      //   // this._sharedService.visibleErrorVideoIcon = false;
      // }
    }
    catch (err) {
      this.preloaderVideo = false;
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get candidate professionally video
   * @return {Promise<void>}
   */
  public async getVideoProfessionally(): Promise<void> {
    this.buttonVideoPreloader = true;
    try {
      await this._candidateService.getVideoProfessionally();
      this.openVerticallyCentered(this.content);
      this.buttonVideoPreloader = false;
      /*this._toastr.success('Request has been sent');*/
    } catch (err) {
      this._sharedService.showRequestErrors(err);
      this.buttonVideoPreloader = false;
    }
  }

  /**
   * Get candidate video
   * @return {Promise<void>}
   */
  public async getCandidateVideo(): Promise<void> {
    try {
      const result = await this._candidateService.getCandidateVideo();
      this.videoObject = result.video;
      this.preloaderPage = false;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open modal
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, size: 'lg' });
  }

  /**
   * Open modal
   * @param content
   */
  public openVerticallyCenter(content) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, size: 'sm', windowClass: 'width-min' });
  }

}
